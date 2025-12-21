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
            ->where('trans_state', '!=', 'PURCHASED')
            ->where('created_at', '<=', now()->subMinutes(15))
            ->chunkById(100, function ($orders) {

                foreach ($orders as $order) {
                    DB::transaction(function () use ($order) {

                        $order = Order::whereKey($order->id)
                            ->lockForUpdate()
                            ->first();
                        $order->delete();

                        Log::info('Order deleted (payment failed)', [
                            'order_id' => $order->id,
                            'trans_state' => $order->trans_state,
                        ]);
                    });
                }
            });
    }
}
