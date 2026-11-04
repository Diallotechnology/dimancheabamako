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
        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            if (! $user->markEmailAsVerified()) {
                flash()->error('Votre e-mail n\'a pas pu être vérifié. Veuillez réessayer.');

                return redirect()->route('verification.notice');
            }

            event(new Verified($user));
        }

        flash()->success('Votre e-mail a été vérifié avec succès!');
        $redirectUrl = $user->isClient() ? '/' : RouteServiceProvider::HOME;

        return redirect()->intended($redirectUrl.'?verified=1');
    }
}
