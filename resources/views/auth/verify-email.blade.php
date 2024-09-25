<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('messages.thanks_for_signing_up') }}
    </div>

    @if (session('status') == 'verification-link-sent')
    <div class="mb-4 font-medium text-sm text-success">
        {{ __('messages.verification_email_resent') }}
    </div>
    <p>NB:{{ __('messages.check_spam') }}</p>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <button class="btn btn-brand w-100">
                    {{ __('messages.didnt_receive_email') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn btn-block btn-sm mt-2">
                {{ __('messages.logout') }}
            </button>
        </form>
    </div>
</x-guest-layout>