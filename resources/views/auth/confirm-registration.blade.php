<x-guest-layout>
    <div class="card mx-auto card-login">
        <div class="card-body text-center">
            <h4 class="mb-3">{{ __('messages.confirm_account_title') }}</h4>
            <p class="mb-4">{{ __('messages.confirm_account_hint') }}</p>

            <form method="POST" action="{{ route('confirm-registration.store', $token) }}">
                @csrf
                <button type="submit" class="btn btn-brand w-100">{{ __('messages.confirm_account_button') }}</button>
            </form>
        </div>
    </div>
</x-guest-layout>
