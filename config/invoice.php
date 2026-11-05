<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Date de bascule des liens de facture
    |--------------------------------------------------------------------------
    |
    | Les factures sont désormais servies par une URL signée (order.invoice.signed).
    | L'ancien lien non signé (order.invoice, basé sur la seule référence N-Genius)
    | n'est conservé que pour les commandes créées AVANT cette date, afin de ne pas
    | casser les e-mails déjà envoyés. À supprimer avec l'ancienne route.
    |
    */

    'legacy_cutoff' => env('INVOICE_LEGACY_CUTOFF', '2026-11-06'),

];
