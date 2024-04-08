<?php

namespace App\Http\Controllers\Auth;

use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $countryNames = new Collection(Countries::getList('fr'));
        $pays = $countryNames->values()->map(function ($row) {
            return [
                'label' => $row, 'value' => $row,
            ];
        });

        return Inertia::render('Auth/Register', ['status' => session('status'), 'pays' => $pays]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'prenom' => 'required|string|max:100',
            'nom' => 'required|string|max:100',
            'pays' => 'required|string|max:100',
            'contact' => 'required|string|max:100',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        dd($request->all());
        $user = User::create([
            'name' => $request->prenom,
            'email' => $request->email,
            'role' => RoleEnum::CUSTOMER->value,
            'password' => Hash::make($request->password),
        ]);

        // $client = Client::create([
        //     'prenom' => $request->prenom,
        //     'nom' => $request->nom,
        //     'contact' => $request->contact,
        //     'email' => $request->email,
        //     'pays' => $pays->nom,
        // ]);

        return \to_route('login');
    }
}
