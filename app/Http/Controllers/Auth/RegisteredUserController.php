<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterUserRequest;
use App\Mail\ConfirmRegistrationMail;
use App\Models\PendingRegistration;
use Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
    public function store(StoreRegisterUserRequest $request)
    {
        if ($request->filled('website')) {
            abort(403);
        }

        $contactE164 = phone($request->contact)->formatE164();
        $token = Str::random(64);

        $pending = PendingRegistration::where('email', $request->email)
            ->orWhere('contact', $contactE164)
            ->first();

        $data = [
            'prenom'     => $request->prenom,
            'nom'        => $request->nom,
            'pays'       => $request->pays,
            'contact'    => $contactE164,
            'email'      => $request->email,
            'role'       => RoleEnum::CUSTOMER->value,
            'password'   => Hash::make($request->password),
            'token'      => $token,
            'expires_at' => now()->addHour(),
        ];

        if ($pending) {
            $pending->update($data);
        } else {
            PendingRegistration::create($data);
        }

        Mail::to($request->email)
            ->send(new ConfirmRegistrationMail($token, app()->getLocale()));

        return back()->with('status', __('messages.register_email_message'));
    }
}
