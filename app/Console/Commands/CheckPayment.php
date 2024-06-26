<?php

namespace App\Console\Commands;

use App\Helper\OrderAPI;
use App\Mail\OrderMail;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Order::whereNotNull('trans_ref')
            ->whereNull('reference')
            ->where('trans_state', '!=', 'PURCHASED')
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
                                    Mail::to('test@exe.com')->send(new OrderMail($order));
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
