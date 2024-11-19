<x-guest-layout>
    <x-slot:title>
        @lang('messages.forgot_password')
        </x-slot>
        <div class="card mx-auto card-login">
            <div class="card-body">
                <div class="mb-4 text-sm text-gray-600">
                    @lang('messages.forgot_password_note')
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                            :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <button class="btn btn-brand w-100">
                            Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
</x-guest-layout>