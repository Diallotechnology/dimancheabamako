<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Countries;
use App\Enum\RoleEnum;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Rules\NotDisposableEmail;
use function Flasher\Prime\flash;
use Illuminate\Support\Collection;
use App\Models\PendingRegistration;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use App\Mail\ConfirmRegistrationMail;
use App\Http\Requests\StoreRegisterUserRequest;

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
    public function store(StoreRegisterUserRequest $request)
    {
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
                'expires_at' => now()->addHours(1),
            ]
        );

        Mail::to($request->email)->send(new ConfirmRegistrationMail($token, app()->getLocale()));

        flash()->success(__('messages.register_success_msg'));

        return back()->with('status', __('messages.register_success_msg'));
    }
}
