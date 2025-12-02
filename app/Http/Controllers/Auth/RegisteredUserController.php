<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Mail\ConfirmRegistrationMail;
use App\Models\PendingRegistration;
use App\Rules\NotDisposableEmail;
use Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

use function Flasher\Prime\flash;

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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'prenom' => 'required|string|max:100',
            'nom' => 'required|string|max:100',
            'pays' => 'required|string|max:50',
            'contact' => ['required', 'phone:international'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail()],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($request->filled('website')) {
            abort(403);
        } // honeypot anti-bots

        $token = Str::random(64);

        PendingRegistration::updateOrCreate(
            ['email' => $request->email],
            [
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'pays' => $request->pays,
                'contact' => phone($request->contact)->formatE164(),
                'email' => $request->email,
                'role' => RoleEnum::CUSTOMER->value,
                'password' => Hash::make($request->password),
                'token' => $token,
                'expires_at' => now()->addHours(2),
            ]
        );

        // Mail::to($request->email)->send(new ConfirmRegistrationMail($token, app()->getLocale()));

        flash()->success('Un email de confirmation vous a été envoyé. verifiez votre boîte mail.');

        return back()->with('status', 'Un email de confirmation vous a été envoyé. verifiez votre boîte mail.');
    }
}
