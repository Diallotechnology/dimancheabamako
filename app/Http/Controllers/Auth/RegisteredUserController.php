<?php

namespace App\Http\Controllers\Auth;

use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Jobs\RegisterMailJob;
use App\Models\Client;
use App\Models\User;
use Countries;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $pays = new Collection(Countries::getList('fr'));

        return view('auth.register', \compact('pays'));
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
            'pays' => 'required|string|max:50',
            'contact' => 'required|string|max:100',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->prenom,
            'email' => $request->email,
            'role' => RoleEnum::CUSTOMER->value,
            'password' => Hash::make($request->password),
        ]);

        Client::create([
            'prenom' => $request->prenom,
            'nom' => $request->nom,
            'contact' => $request->contact,
            'email' => $request->email,
            'pays' => $request->pays,
        ]);

        event(new Registered($user));
        RegisterMailJob::dispatch($user);

        Auth::login($user);
        toastr()->success('Votre inscription a été valider avec success!');

        return redirect('/');
    }
}
