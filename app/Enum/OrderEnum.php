<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderEnum: string
{
    case SAVE = 'Enregistré';
    case EN_ATTENTE = 'En attente';
    case EN_COURS = 'En cours';
    case TERMINE = 'Terminé';
    case STOCK = 'Stock épuisé';

    public static function all(): array
    {
        return array_map(function (self $role) {
            return [
                'id' => $role->value,
                'name' => $role->value,
            ];
        }, self::cases());
    }
}
