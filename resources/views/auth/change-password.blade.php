<x-guest-layout>
    <!-- /Logo -->
    <h4 class="mb-2">Changement de Mot de Passe Requis 🔒</h4>
    <h6>
        NB: Utilisez au moins huit (8) caractères, mélangez majuscules, minuscules, chiffres et
        caractères spéciaux.
    </h6>
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
    <form id="formAuthentication" class="mb-3" action="{{ route('change_password') }}" method="post">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password">Nouveau mot de passe</label>
            <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password"
                    placeholder="Entrez votre mot de passe" aria-describedby="password" required />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password_confirmation">Confirmé le mot de passe</label>
            <div class="input-group input-group-merge">
                <input type="password" id="password_confirmation" class="form-control" name="password_confirmation"
                    placeholder="Confirmé le mot de passe" aria-describedby="password" required />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">Valider</button>
        <div class="text-center">
            <a href="{{ route('login') }}">
                <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                Se connecter
            </a>
        </div>
    </form>
</x-guest-layout>