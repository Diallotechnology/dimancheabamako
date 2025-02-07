<?php

namespace App\Console\Commands;

use App\Helper\OrderAPI;
use App\Models\PayLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeletePayByLink extends Command
{
    use OrderAPI;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-pay-by-link';

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
        PayLink::whereNotNull('trans_ref')
            ->where('etat', 'Pending')
            ->whereNull('trans_state')
            ->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    $responseData = $this->getOrderStatut($order->trans_ref);
                    // dd($responseData);
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
                                // $this->cancelPaymentLink($order->trans_ref);
                                $order->updateOrFail(['etat' => 'Expire']);
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
