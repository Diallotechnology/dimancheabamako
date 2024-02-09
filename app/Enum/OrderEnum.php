<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderEnum: string
{
    case SAVE = 'Enregistré';
    case EN_ATTENTE = 'En attente';
    case EN_COURS = 'En cours';
    case TERMINE = 'Terminé';
}
