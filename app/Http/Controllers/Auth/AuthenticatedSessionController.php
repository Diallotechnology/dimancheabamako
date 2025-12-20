<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\UpdateUserProfilRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Service\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $oldSessionId = session()->getId();
        $request->authenticate();

        $user = User::where('email', $request->email)->first();

        if (! $user->change_password) {
            Auth::logout();

            return redirect()->route('change.password', ['email' => $user->email]);
        }
        // Save the 'user_id' from the session if it exists
        $cart = app(CartService::class);
        $cart->mergeGuestCartToUser(Auth::id(), $oldSessionId);
        $request->session()->regenerate();

        if ($request->user()->isClient()) {
            return redirect()->intended('/');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function update(UpdateUserProfilRequest $request, User $user)
    {
        if (! empty($request->password_confirmation) and ! empty($request->password)) {
            $user->forceFill([
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'name' => $request->name,
            ])->save();
        } else {
            $user->update(['email' => $request->email, 'name' => $request->name]);
        }
        flash()->success('Profil mise à jour avec success!');

        return back();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // $oldSessionId = session()->getId();

        // $cart = app(CartService::class);
        // $cart->mergeGuestCartToUser(Auth::id(), $oldSessionId);
        Auth::guard('web')->logout();
        // Invalidate the session
        $request->session()->invalidate();

        // Redirect to the login route
        return to_route('login');
    }
}
