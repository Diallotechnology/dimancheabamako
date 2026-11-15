<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Shipping;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Lecture seule : à lancer manuellement (en production notamment), ne modifie rien.
 */
final class CheckShippingsCoherence extends Command
{
    private const LIST_LIMIT = 50;

    /**
     * @var string
     */
    protected $signature = 'app:check-shippings-coherence';

    /**
     * @var string
     */
    protected $description = 'Liste les tarifs shippings dont la zone n\'est pas desservie par leur transporteur (jamais retenus par ShippingService::resolve)';

    public function handle(): int
    {
        $query = Shipping::query()->whereNotExists(function (Builder $sub): void {
            $sub->select(DB::raw(1))
                ->from('transport_zone')
                ->whereColumn('transport_zone.transport_id', 'shippings.transport_id')
                ->whereColumn('transport_zone.zone_id', 'shippings.zone_id');
        });
        $count = $query->count();

        if ($count === 0) {
            $this->info('OK : tous les tarifs ont une zone desservie par leur transporteur.');

            return self::SUCCESS;
        }

        $this->table(
            ['id', 'transport_id', 'zone_id', 'poids_id', 'montant'],
            $query->orderBy('id')->limit(self::LIST_LIMIT)->get()
                ->map(fn (Shipping $s): array => [$s->id, $s->transport_id, $s->zone_id, $s->poids_id, $s->montant])
                ->all(),
        );

        if ($count > self::LIST_LIMIT) {
            $this->line(sprintf('… et %d autre(s) non affichée(s).', $count - self::LIST_LIMIT));
        }

        $this->error(sprintf(
            'ÉCHEC : %d tarif(s) incohérent(s). À corriger (zone desservie) ou supprimer à la main ; rien n\'est modifié par cette commande.',
            $count,
        ));

        return self::FAILURE;
    }
}
