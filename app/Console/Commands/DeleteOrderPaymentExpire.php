<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class DeleteOrderPaymentExpire extends Command
{
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

                        // 1️⃣ Supprimer la commande (cause)
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
