<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helper\OrderAPI;
use App\Models\Order;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                        $order = Order::with('client')
                            ->lockForUpdate()
                            ->find($order->id);

                        if (! $order) {
                            return;
                        }

                        $client = $order->client;

                        // 1️⃣ Supprimer la commande (cause)
                        $order->delete();

                        Log::info('Order deleted (abandoned / failed)', [
                            'order_id'   => $order->id,
                            'state'      => $order->trans_state,
                        ]);

                        // 2️⃣ Nettoyer le client si fictif
                        if (! $client) {
                            return;
                        }

                        $hasPaidOrders = $client->orders()
                            ->where('trans_state', 'PURCHASED')
                            ->exists();

                        if ($hasPaidOrders) {
                            return;
                        }

                        $client->delete();

                        Log::info('Client deleted (no paid orders)', [
                            'client_id' => $client->id,
                        ]);
                    });
                }
            });
    }
}
