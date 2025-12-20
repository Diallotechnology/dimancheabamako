<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class NotifOrderStockIssue extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Order $order, protected Product $product)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Problème de stock')
            ->line('Un problème de stock a été détecté.')
            ->line('Commande : #'.$this->order->id)
            ->line('Produit : '.$this->product->reference)
            ->line('Quantité demandée : '.($this->product->pivot?->quantity ?? 'N/A'))
            ->line('Merci de vérifier le stock avant validation.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
