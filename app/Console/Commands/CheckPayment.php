<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helper\OrderAPI;
use App\Jobs\OrderMailJob;
use App\Mail\CancelOrderMail;
use App\Models\Order;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        // Ajouter un verrou pour les enregistrements sélectionnés
        Order::whereNotNull('trans_ref')
            ->whereNull('reference')
            ->whereNull('trans_state')
            ->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    DB::transaction(function () use ($order) {
                        // Verrouiller l'enregistrement pour éviter les modifications concurrentes
                        $order = Order::where('id', $order->id)->lockForUpdate()->first();

                        $responseData = $this->getOrderStatut($order->trans_ref);
                        if ($responseData) {
                            if (isset($responseData['_embedded']['payment'][0]['state'])) {
                                $paymentState = $responseData['_embedded']['payment'][0]['state'];
                                if ($paymentState === 'PURCHASED') {
                                    $order->update(['trans_state' => $paymentState]);

                                    // Mettre à jour les stocks
                                    $order->products->each(function ($product) use ($order) {
                                        if ($product->stock >= $product->pivot->quantity) {
                                            $product->decrement('stock', $product->pivot->quantity);
                                        } else {
                                            // Gérer l'erreur de stock insuffisant
                                            Mail::to($order->client->email)->send(new CancelOrderMail($order));
                                            $order->delete();

                                            return; // Terminer le traitement de cette commande
                                        }
                                    });

                                    // Générer un ID et envoyer un mail
                                    $order->generateId();
                                    OrderMailJob::dispatch($order);
                                }
                            } else {
                                Log::warning("The 'state' key was not found in the transaction");
                            }
                        } else {
                            Log::error('Failed to retrieve order status', ['reference' => $order->trans_ref]);
                        }
                    });
                }
            });
    }

    private function cart_clear(): void
    {
        if (! empty(session('user_id'))) {
            // Utiliser l'identifiant de session existant
            $userId = session()->get('user_id');
            CartFacade::session($userId)->clear();
        }
    }
}
