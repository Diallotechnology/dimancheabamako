<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\UpdateUserProfilRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
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
        $request->authenticate();

        $user = User::where('email', $request->email)->first();

        if (! $user->change_password) {
            Auth::logout();

            return redirect()->route('change.password', ['email' => $user->email]);
        } else {
            // Save the 'user_id' from the session if it exists
            $key = ! empty($request->session()->get('user_id')) ? $request->session()->get('user_id') : null;
            $cart = $request->session()->get($key.'_cart_items');
            $request->session()->regenerate();
            if ($key) {
                session()->put(['user_id' => $key, $key.'_cart_items' => $cart]);
            }
            if ($request->user()->isClient()) {
                return redirect()->intended('/');
            } else {
                return redirect()->intended(RouteServiceProvider::HOME);
            }

        }
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
        toastr()->success('Profil mise à jour avec success!');

        return back();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log out the user from the 'web' guard
        Auth::guard('web')->logout();

        // Save the 'user_id' from the session if it exists
        $key = ! empty($request->session()->get('user_id')) ? $request->session()->get('user_id') : null;
        $cart = $request->session()->get($key.'_cart_items');
        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        // dd($key);
        // Restore the 'user_id' to the session if it was present
        if ($key) {
            session()->put(['user_id' => $key, $key.'_cart_items' => $cart]);
        }

        // Redirect to the login route
        return to_route('login');
    }
}
