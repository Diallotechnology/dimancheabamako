<?php

namespace App\Console\Commands;

use App\Helper\OrderAPI;
use App\Jobs\OrderMailJob;
use App\Mail\CancelOrderMail;
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
                                    // Mettre à jour les stocks ici
                                    $order->products->each(function ($product) use ($order) {
                                        // Vérifier le stock et mettre à jour
                                        if ($product->stock >= $product->pivot->quantity) {
                                            $product->decrement('stock', $product->pivot->quantity);
                                        } else {
                                            // Gérer l'erreur de stock insuffisant ici, si nécessaire
                                            // Envoyer un email au client
                                            Mail::to($order->client->email)->send(new CancelOrderMail($order));
                                            $order->delete();

                                        }
                                    });
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
