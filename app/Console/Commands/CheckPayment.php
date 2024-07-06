<?php

namespace App\Console\Commands;

use App\Helper\OrderAPI;
use App\Jobs\OrderMailJob;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckPayment extends Command
{
    use OrderAPI;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check-order-payment and send email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Order::whereNotNull('trans_ref')
            ->whereNull('reference')
            ->whereNull('trans_state')
            ->whereDate('created_at', today())
            ->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    $responseData = $this->getOrderStatut($order->trans_ref);
                    if ($responseData) {
                        DB::beginTransaction();

                        try {
                            if (isset($responseData['_embedded']['payment'][0]['state'])) {
                                $paymentState = $responseData['_embedded']['payment'][0]['state'];
                                if ($paymentState === 'PURCHASED') {
                                    $order->updateOrFail(['trans_state' => $paymentState]);
                                    $order->generateId();
                                    OrderMailJob::dispatch($order);
                                }
                            } else {
                                Log::warning("The 'state' key was not found in the transaction");
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
