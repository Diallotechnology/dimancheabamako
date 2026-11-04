<?php

declare(strict_types=1);

arch('app code uses strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('no debugging helpers are left in the code')
    ->expect(['dd', 'dump', 'var_dump', 'ray', 'die', 'exit'])
    ->not->toBeUsed();

arch('models extend Eloquent and live in App\Models')
    ->expect('App\Models')
    ->classes()
    ->toExtend(Illuminate\Database\Eloquent\Model::class)
    ->ignoring('App\Models\Scopes');

arch('enums are backed enums in App\Enum')
    ->expect('App\Enum')
    ->toBeEnums();

arch('controllers do not depend on Livewire components')
    ->expect('App\Http\Controllers')
    ->not->toUse('App\Livewire');

arch('services and helpers do not depend on HTTP controllers')
    ->expect(['App\Service', 'App\Helper'])
    ->not->toUse('App\Http\Controllers');
