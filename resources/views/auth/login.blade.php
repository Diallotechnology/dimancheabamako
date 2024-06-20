<x-guest-layout>
    <!-- Session Status -->
    <div class="card mx-auto card-login">
        <div class="card-body">
            <h4 class="card-title mb-4 h2">Se connecter</h4>
            <x-auth-session-status class="mb-4" :status="session('status')" />
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                        required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                        autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div class="mb-3">
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="float-end font-sm text-muted">
                        Mot de passe oublié ?
                    </a>
                    @endif
                </div>
                <div class="d-flex justify-content-center my-4">
                    <p class="me-2">Vous n'avez de un compte?</p>
                    <div>
                        <a href="{{ route('register') }}" class="underline text-end text-sm">
                            S'inscrire
                        </a>
                    </div>
                </div>
                <div class="mb-4">
                    <button class="btn btn-brand w-100">
                        Valider
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>