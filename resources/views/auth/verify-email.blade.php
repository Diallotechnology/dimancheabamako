<x-shop-layout>
    <x-slot:title>{{ __('messages.didnt_receive_email') }}</x-slot:title>

    <x-shop.auth-card :title="__('messages.didnt_receive_email')" :intro="__('messages.thanks_for_signing_up')">
        @if (session('status') == 'verification-link-sent')
            <p role="status" class="mb-4 rounded-lg bg-success/10 px-3 py-2 text-sm font-medium text-success">
                {{ __('messages.verification_email_resent') }}
                <span class="mt-1 block font-normal">{{ __('messages.check_spam') }}</span>
            </p>
        @endif

        <div class="space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-shop.button type="submit" size="lg" class="w-full">{{ __('messages.didnt_receive_email') }}</x-shop.button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-shop.button type="submit" variant="outline" class="w-full">{{ __('messages.logout') }}</x-shop.button>
            </form>
        </div>
    </x-shop.auth-card>
</x-shop-layout>
