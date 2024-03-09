<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    public function rootView(Request $request)
    {
        if (request()->is('admin/*')) {

            return 'admin';
        } else {
            return 'app';
        }

        // return parent::rootView($request);
    }

    // protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'locale' => session()->has('locale') ? session('locale') : session()->put('locale', 'fr'),
        ];
    }
}
