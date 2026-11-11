<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Collection;

/**
 * Options de liste déroulante pour le back-office : `{ label, value }`, valeur toujours en chaîne
 * (les composants de sélection comparent des chaînes, cf. type `Option` côté Vue).
 */
final class Options
{
    /**
     * @template TItem
     *
     * @param  iterable<array-key, TItem>  $items
     * @param  Closure(TItem): (string|int|float)  $label
     * @param  (Closure(TItem): (string|int))|null  $value  par défaut l'attribut `id`
     * @return Collection<int, array{label: string, value: string}>
     */
    public static function from(iterable $items, Closure $label, ?Closure $value = null): Collection
    {
        $value ??= static fn (mixed $item): mixed => data_get($item, 'id');

        return collect($items)
            ->values()
            ->map(static fn (mixed $item): array => [
                'label' => (string) $label($item),
                'value' => (string) $value($item),
            ]);
    }

    /**
     * Libellés servant aussi de valeurs (noms de pays).
     *
     * @param  iterable<array-key, string>  $names
     * @return Collection<int, array{label: string, value: string}>
     */
    public static function fromNames(iterable $names): Collection
    {
        return self::from($names, static fn (string $name): string => $name, static fn (string $name): string => $name);
    }
}
