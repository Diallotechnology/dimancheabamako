<x-guest-layout>
    <div class="card mx-auto card-login">
        <div class="card-body">
            <!-- /Logo -->
            <h4 class="mb-2">@lang('messages.password_change_required') 🔒</h4>
            <h6>@lang('messages.password_note')</h6>
            @error('email')
            <div class="alert alert-danger d-flex" role="alert">
                <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i
                        class="bx bx-store fs-6"></i></span>
                <div class="d-flex flex-column ps-1">
                    <h6 class="alert-heading d-flex align-items-center mb-1">Error!!</h6>
                    <span>{{ $message }}</span>
                </div>
            </div>
            @enderror
            <x-auth-session-status class="mb-4" :status="session('status')" />
            <form id="formAuthentication" class="mb-3" action="{{ route('password.store') }}" method="post">
                @csrf
                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3 form-password-toggle">
                    <label class="form-label" for="password">@lang('messages.new_password')</label>
                    <div class="input-group input-group-merge">
                        <input type="password" id="password" class="form-control" name="password"
                            placeholder="Entrez votre mot de passe" aria-describedby="password" required />

                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div class="mb-3 form-password-toggle">
                    <label class="form-label" for="password_confirmation">@lang('messages.confirm_password')</label>
                    <div class="input-group input-group-merge">
                        <input type="password" id="password_confirmation" class="form-control"
                            name="password_confirmation" placeholder="{{ __('messages.confirm_password') }}"
                            aria-describedby="password" required />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
                <button type="submit" class="btn btn-primary d-grid w-100 mb-3">Valider</button>
                <div class="text-center">
                    <a href="{{ route('login') }}">
                        @lang('messages.login')
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
