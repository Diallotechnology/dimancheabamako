<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterUserRequest;
use App\Service\PendingRegistrationService;
use Countries;
use Illuminate\Support\Collection;
use Illuminate\View\View;

use function to_route;

final class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $pays = new Collection(Countries::getList('fr'));

        return view('auth.register', compact('pays'));
    }

    /**
     * Handle an incoming registration request.
     *
     * L'inscription en attente (jamais écrasée par un tiers) et l'envoi du lien sont dans le service.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(StoreRegisterUserRequest $request, PendingRegistrationService $pendings)
    {
        if ($request->filled('website')) {
            abort(403);
        }

        $opened = $pendings->request([
            'prenom' => (string) $request->prenom,
            'nom' => (string) $request->nom,
            'pays' => (string) $request->pays,
            'contact' => phone($request->contact)->formatE164(),
            'email' => (string) $request->email,
        ], (string) $request->password, app()->getLocale());

        // Téléphone déjà tenu par l'inscription en attente d'un autre e-mail : on ne la touche pas.
        if (! $opened) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['contact' => __('messages.register_phone_conflict')]);
        }

        return to_route('register-email', 0)->with('status', __('messages.register_email_message'));
    }
}
