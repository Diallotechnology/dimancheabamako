<x-guest-layout>
    <x-slot:title>
        @lang('messages.register')
        </x-slot>
        <div class="card mx-auto card-login">
            <div class="card-body">
                <x-auth-session-status class="mb-4" :status="session('status')" />
                <h4 class="card-title mb-4 h2"> @lang('messages.register')</h4>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <input type="text" name="website" style="display:none">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mt-4">
                                <!-- Name -->
                                <x-input-label for="prenom" :value="__('messages.first_name')" />
                                <x-text-input id="prenom" class="block mt-1 w-full" type="text" name="prenom"
                                    :value="old('prenom')" required autofocus autocomplete="prenom" />
                                <x-input-error :messages="$errors->get('prenom')" class="mt-2" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mt-4">
                                <!-- Name -->
                                <x-input-label for="nom" :value="__('messages.last_name')" />
                                <x-text-input id="nom" class="block mt-1 w-full" type="text" name="nom"
                                    :value="old('nom')" required autofocus autocomplete="nom" />
                                <x-input-error :messages="$errors->get('nom')" class="mt-2" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mt-4">
                                <x-select name="pays" :label="__('messages.country')">
                                    @foreach ($pays as $row)
                                    <option {{ old('pays')==$row ? 'selected' : '' }} value="{{ $row }}">{{ $row }}
                                    </option>
                                    @endforeach
                                </x-select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mt-4">
                                <!-- Name -->
                                <x-input-label for="contact" :value="__('messages.contact_with_code')" />
                                <input type="tel" name="contact" class="form-input block mt-1 w-full form-control"
                                    placeholder="+223XXXXXXXX" required />
                                <x-input-error :messages="$errors->get('contact')" class="mt-2" />

                            </div>
                        </div>
                        <div class="col-md-12">
                            <!-- Email Address -->
                            <div class="mt-4">
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                    :value="old('email')" required autocomplete="username" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>
                        <!-- Password -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Mot de passe')" />

                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                                required autocomplete="new-password" />

                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />

                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                                name="password_confirmation" required autocomplete="new-password" />

                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <div class="d-flex justify-content-center my-4">
                        <p class="me-2">@lang('messages.already_have_account')</p>
                        <div>
                            <a href="{{ route('login') }}" class="underline text-end text-sm">
                                @lang('messages.login')
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