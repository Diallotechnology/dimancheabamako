<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enum\OrderEnum;
use App\Helper\OrderAPI;
use App\Jobs\OrderMailJob;
use App\Mail\CancelOrderMail;
use App\Models\Order;
use App\Notifications\NotifOrderStockIssue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Throwable;

final class CheckPayment extends Command
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
        Order::query()
            ->whereNotNull('trans_ref')
            ->whereNull('reference')
            ->chunkById(100, function ($orders) {

                foreach ($orders as $order) {

                    DB::transaction(function () use ($order) {

                        /** @var Order $order */
                        $order = Order::whereKey($order->id)
                            ->lockForUpdate()
                            ->first();

                        try {
                            $data = $this->getOrderStatus($order->trans_ref);
                        } catch (Throwable $e) {
                            Log::error('Payment status fetch failed', [
                                'order_id' => $order->id,
                                'error' => $e->getMessage(),
                            ]);

                            return;
                        }

                        $payment = data_get($data, '_embedded.payment.0');
                        $state = $payment['state'] ?? null;
                        if (! $state) {
                            Log::warning('Payment state missing', [
                                'order_id' => $order->id,
                                'response' => $data,
                            ]);

                            return;
                        }

                        // 🔴 Paiement refusé / annulé / expiré
                        if (in_array($state, ['FAILED', 'CANCELLED', 'EXPIRED'], true)) {
                            $order->update(['trans_state' => $state]);

                            $order->delete(); // autorisé ici

                            return;
                        }

                        // 🟢 Paiement validé
                        if ($state === 'PURCHASED') {

                            $order->update([
                                'trans_state' => $state,
                            ]);

                            // Vérification stock AVANT décrément
                            foreach ($order->products as $product) {

                                if ($product->is_preorder) {
                                    continue;
                                }

                                if ($product->stock < $product->pivot->quantity) {
                                    $order->update(['etat' => OrderEnum::STOCK->value]);
                                    Notification::route('mail', 'topmariage.mali@gmail.com')
                                        ->notify(new NotifOrderStockIssue($order, $product));

                                    return;
                                }
                            }

                            // Décrément réel
                            foreach ($order->products as $product) {
                                if (! $product->is_preorder) {
                                    $product->decrement(
                                        'stock',
                                        $product->pivot->quantity
                                    );
                                }
                            }

                            $order->generateId();
                            OrderMailJob::dispatch($order);
                        }
                    });
                }
            });
        // Ajouter un verrou pour les enregistrements sélectionnés
        // Order::whereNotNull('trans_ref')
        //     ->whereNull('reference')
        //     ->whereNull('trans_state')
        //     ->chunk(100, function ($orders) {
        //         foreach ($orders as $order) {
        //             DB::transaction(function () use ($order) {
        //                 // Verrouiller l'enregistrement pour éviter les modifications concurrentes
        //                 $order = Order::where('id', $order->id)->lockForUpdate()->first();

        //                 $responseData = $this->getOrderStatut($order->trans_ref);
        //                 if ($responseData) {
        //                     if (isset($responseData['_embedded']['payment'][0]['state'])) {
        //                         $paymentState = $responseData['_embedded']['payment'][0]['state'];
        //                         if ($paymentState === 'PURCHASED') {
        //                             $order->update(['trans_state' => $paymentState]);

        //                             // Mettre à jour les stocks
        //                             $order->products->each(function ($product) use ($order) {
        //                                 if ($product->is_preorder == \false and $product->stock >= $product->pivot->quantity) {
        //                                     $product->decrement('stock', $product->pivot->quantity);
        //                                 } else {
        //                                     // Gérer l'erreur de stock insuffisant
        //                                     Mail::to($order->client->email)->send(new CancelOrderMail($order));
        //                                     $order->delete();
        //                                     return; // Terminer le traitement de cette commande
        //                                 }
        //                             });

        //                             // Générer un ID et envoyer un mail
        //                             $order->generateId();
        //                             OrderMailJob::dispatch($order);
        //                         }
        //                     } else {
        //                         Log::warning("The 'state' key was not found in the transaction");
        //                     }
        //                 } else {
        //                     Log::error('Failed to retrieve order status', ['reference' => $order->trans_ref]);
        //                 }
        //             });
        //         }
        //     });
    }
}
