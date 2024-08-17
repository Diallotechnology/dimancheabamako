<?php

namespace App\Console\Commands;

use App\Models\PayLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckPayByLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-pay-by-link';

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
            ->whereNull('trans_state')
            ->where('etat', 'Pending')
            ->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    $responseData = $this->getOrderStatut($order->trans_ref);
                    if ($responseData) {
                        DB::beginTransaction();
                        try {
                            if (isset($responseData['_embedded']['payment'][0]['state'])) {
                                $paymentState = $responseData['_embedded']['payment'][0]['state'];
                                if ($paymentState === 'PURCHASED') {
                                    $order->updateOrFail(['trans_state' => $paymentState, 'etat' => 'Valid']);
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
