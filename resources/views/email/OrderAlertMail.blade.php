<x-mail::message>
# 📦 Nouvelle commande reçue

Bonjour,  
Une nouvelle commande vient d’être enregistrée sur la plateforme.

---

## 🧾 Informations sur la commande

- **Référence :** {{ $order->reference }}
- **Date :** {{ $order->created_at }}
- **Client :** {{ $order->client->prenom }} {{ $order->client->nom }}
- **Téléphone :** {{ $order->client->contact }}
- **Email :** {{ $order->client->email }}

---

## 🛒 Détails des articles

<x-mail::table>
| Produit | Quantité | Prix | Montant |
|:--------|:--------:|-------------:|------:|
@foreach ($order->products as $item)
| {{ $item->reference }} | {{ $item->pivot->quantity }} | {{ number_format($item->prix, 0, ',', ' ') }} F | {{ number_format($item->pivot->montant, 0, ',', ' ') }} F |
@endforeach
</x-mail::table>

---

## 💰 Total de la commande

<x-mail::panel>
<strong>{{ number_format($order->totaux, 0, ',', ' ') }} FCFA</strong>
</x-mail::panel>

---

Merci,  
**{{ config('app.name') }}**
</x-mail::message>
