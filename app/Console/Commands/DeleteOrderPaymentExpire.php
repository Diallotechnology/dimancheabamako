<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helper\OrderAPI;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DeleteOrderPaymentExpire extends Command
{
    use OrderAPI;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-order-payment-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Order::query()
            ->whereNull('reference')
            ->where('created_at', '<=', now()->subMinutes(10))
            ->where(function ($q) {
                $q->whereNull('trans_state')
                    ->orWhereIn('trans_state', ['FAILED', 'CANCELLED', 'EXPIRED']);
            })
            ->chunkById(100, function ($orders) {

                foreach ($orders as $order) {
                    DB::transaction(function () use ($order) {

                        /** @var Order|null $order */
                        $order = Order::lockForUpdate()->find($order->id);

                        if (! $order) {
                            return;
                        }

                        // 0️⃣ Statut réel chez N-Genius avant de libérer le stock
                        if ($order->trans_ref !== null) {
                            try {
                                $data = $this->getOrderStatus($order->trans_ref);
                            } catch (Throwable $e) {
                                Log::error('Payment status fetch failed', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage(),
                                ]);

                                return; // on ne touche à rien, prochain passage
                            }

                            // Payée : check-payment la traitera normalement.
                            if (data_get($data, '_embedded.payment.0.state') === 'PURCHASED') {
                                return;
                            }
                        }

                        // 1️⃣ Restituer le stock réservé, puis supprimer la commande (cause)
                        $order->releaseStock();
                        $order->delete();

                        // 2️⃣ Recharger + verrouiller le client
                        $client = Client::lockForUpdate()->find($order->client_id);

                        if (! $client) {
                            return;
                        }

                        // 3️⃣ Si client lié à un user → NEVER DELETE
                        if ($client->user()->exists()) {
                            return;
                        }

                        // 4️⃣ Si au moins une commande payée → KEEP
                        $hasPaidOrders = $client->orders()
                            ->where('trans_state', 'PURCHASED')
                            ->exists();

                        if ($hasPaidOrders) {
                            return;
                        }

                        // 5️⃣ Safe delete (guest fictif uniquement)
                        $client->delete();
                    });
                }
            });
    }
}
