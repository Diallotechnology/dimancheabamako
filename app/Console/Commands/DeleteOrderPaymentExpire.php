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
        // $expiredAt = now()->subMinutes(10);

        Order::query()
            ->whereNull('reference')
            // ->where('created_at', '<=', $expiredAt)
            ->whereIn('trans_state', ['FAILED', 'CANCELLED', 'EXPIRED'])
            ->orWhere(function ($q) {
                $q->whereNull('trans_state')
                    ->where('created_at', '<=', now()->subMinutes(15));
            })
            ->chunkById(100, function ($orders) {

                foreach ($orders as $order) {
                    DB::transaction(function () use ($order) {

                        $order = Order::whereKey($order->id)
                            ->lockForUpdate()
                            ->first();

                        // Double sécurité
                        if (! in_array($order->trans_state, ['FAILED', 'CANCELLED', 'EXPIRED'], true)) {
                            return;
                        }

                        $order->delete();

                        Log::info('Order deleted (payment failed)', [
                            'order_id' => $order->id,
                            'trans_state' => $order->trans_state,
                        ]);
                    });
                }
            });

        // Order::whereNotNull('trans_ref')
        //     ->whereNull('reference')
        //     ->whereNull('trans_state')
        //     ->chunk(100, function ($orders) {
        //         foreach ($orders as $order) {
        //             DB::transaction(function () use ($order) {
        //                 // Verrouiller la commande pour empêcher les accès concurrents
        //                 $order = Order::where('id', $order->id)->lockForUpdate()->first();

        //                 // Récupérer l'état de la commande via l'API
        //                 $responseData = $this->getOrderStatut($order->trans_ref);
        //                 if ($responseData) {
        //                     try {
        //                         // Parse `createDateTime` depuis les données reçues
        //                         $createDateTime = Carbon::parse($responseData['createDateTime']);
        //                         $currentTime = Carbon::now();

        //                         // Calculer la différence en minutes
        //                         $minutesDifference = $currentTime->diffInMinutes($createDateTime);

        //                         // Vérifier l'état du paiement
        //                         $paymentState = $responseData['_embedded']['payment'][0]['state'] ?? null;

        //                         if ($minutesDifference >= 5 && $paymentState !== 'PURCHASED') {
        //                             // Annuler le lien de paiement et supprimer la commande
        //                             // $this->cancelPaymentLink($order->trans_ref);
        //                             $order->delete();

        //                             Log::info('Order cancelled due to timeout', [
        //                                 'order_id' => $order->id,
        //                                 'trans_ref' => $order->trans_ref,
        //                                 'minutes_elapsed' => $minutesDifference,
        //                             ]);
        //                         }
        //                     } catch (Exception $e) {
        //                         Log::error('Failed to process order', [
        //                             'order_id' => $order->id,
        //                             'trans_ref' => $order->trans_ref,
        //                             'error' => $e->getMessage(),
        //                         ]);
        //                         throw $e; // Relancer pour annuler la transaction
        //                     }
        //                 } else {
        //                     Log::error('Failed to retrieve order status', ['trans_ref' => $order->trans_ref]);
        //                 }
        //             });
        //         }
        //     });
    }
}
