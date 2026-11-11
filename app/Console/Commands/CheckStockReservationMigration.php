<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

final class CheckStockReservationMigration extends Command
{
    private const LIST_LIMIT = 50;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-stock-reservation-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie avant déploiement qu\'aucune commande en cours n\'a été créée avant la réservation de stock';

    public function handle(): int
    {
        $query = Order::query()->whereNull('reference')->whereNotNull('trans_ref');
        $count = $query->count();

        if ($count === 0) {
            $this->info('OK : aucune commande en cours (reference NULL et trans_ref renseigné). La bascule peut avoir lieu.');

            return self::SUCCESS;
        }

        $this->table(
            ['id', 'trans_ref'],
            $query->orderBy('id')->limit(self::LIST_LIMIT)->get(['id', 'trans_ref'])
                ->map(fn (Order $order): array => [$order->id, $order->trans_ref])
                ->all(),
        );

        if ($count > self::LIST_LIMIT) {
            $this->line(sprintf('… et %d autre(s) non affichée(s).', $count - self::LIST_LIMIT));
        }

        $this->error(sprintf(
            'ÉCHEC : %d commande(s) en cours n\'ont pas réservé de stock. Les traiter (paiement finalisé ou annulation) avant la bascule.',
            $count,
        ));

        return self::FAILURE;
    }
}
