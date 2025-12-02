<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

final class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            toastr()->success('Votre e-mail a été vérifié avec succès!');
            $redirectUrl = $request->user()->isClient() ? '/' : RouteServiceProvider::HOME;

            return redirect()->intended($redirectUrl.'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            toastr()->success('Votre e-mail a été vérifié avec succès!');
            $redirectUrl = $request->user()->isClient() ? '/' : RouteServiceProvider::HOME;

            return redirect()->intended($redirectUrl.'?verified=1');
        }
    }
}
