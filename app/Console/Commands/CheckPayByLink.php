<?php

namespace App\Console\Commands;

use App\Helper\OrderAPI;
use App\Models\PayLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckPayByLink extends Command
{
    use OrderAPI;

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
                    DB::transaction(function () use ($order) {
                        // Verrouiller la ligne pour éviter les modifications concurrentes
                        $order = PayLink::where('id', $order->id)->lockForUpdate()->first();

                        try {
                            $responseData = $this->getOrderStatut($order->trans_ref);

                            if ($responseData) {
                                if (isset($responseData['_embedded']['payment'][0]['state'])) {
                                    $paymentState = $responseData['_embedded']['payment'][0]['state'];

                                    if ($paymentState === 'PURCHASED') {
                                        $order->updateOrFail([
                                            'trans_state' => $paymentState,
                                            'etat' => 'Valid',
                                        ]);
                                        $this->info("Order {$order->id} updated successfully.");
                                    } else {
                                        $this->warn("Order {$order->id} is not completed (state: {$paymentState}).");
                                    }
                                } else {
                                    Log::warning("The 'state' key was not found in the transaction", ['trans_ref' => $order->trans_ref]);
                                }
                            } else {
                                Log::error('Failed to retrieve order status', ['trans_ref' => $order->trans_ref]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to update order', [
                                'order' => $order->trans_ref,
                                'error' => $e->getMessage(),
                            ]);
                            throw $e; // Annuler la transaction en cas d'erreur
                        }
                    });
                }
            });

        $this->info('Check-pay-by-link process completed.');

    }
}
