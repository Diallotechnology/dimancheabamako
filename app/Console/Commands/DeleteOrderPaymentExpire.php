<?php

namespace App\Console\Commands;

use App\Helper\OrderAPI;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteOrderPaymentExpire extends Command
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
        Order::whereNotNull('trans_ref')
            ->whereNull('reference')
            ->whereNull('trans_state')
            ->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    $responseData = $this->getOrderStatut($order->trans_ref);
                    if ($responseData) {
                        DB::beginTransaction();

                        try {
                            // Parse the createDateTime
                            $createDateTime = Carbon::parse($responseData['createDateTime']);

                            // Get the current time
                            $currentTime = Carbon::now();

                            // Calculate the time difference in minutes
                            $minutesDifference = $currentTime->diffInMinutes($createDateTime);
                            $paymentState = $responseData['_embedded']['payment'][0]['state'];
                            if ($minutesDifference >= 5 && $paymentState !== 'PURCHASED') {
                                $this->cancelPaymentLink($order->trans_ref);
                                $order->delete();
                            }

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Failed to update order or send mail', ['order' => $order->trans_ref, 'error' => $e->getMessage()]);
                        }
                    } else {
                        Log::error('Failed to retrieve order status', ['reference' => $order->trans_ref]);
                    }
                }
            });
    }
}
