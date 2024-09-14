<div class="col-md-6 mx-auto">
    <!-- Affichage du captcha -->
    <img src="{{ Captcha::src('math') }}" alt="captcha" class="captcha-img">


    <!-- Champ input pour le captcha -->
    <input type="text" name="captcha" class="form-control @error('captcha') is-invalid @enderror"
        placeholder="Please insert captcha" required>

    <!-- Message d'erreur personnalisé -->
    @error('captcha')
    <div class="invalid-feedback">{{ $message }}</div>
    @else
    <div class='invalid-feedback'>Ce champ est obligatoire.</div>
    @enderror

    <div class="valid-feedback"></div>
</div>