<x-guest-layout>
    @if (session()->has('status'))
    <div class="apple-success mb-3">
        {{ session('status') }}
    </div>
    @endif
    @if ($show)

    <h4 class="text-center mb-4 h2">
        <a href="/" role="button" class="btn btn-primary">Contunuer la visite du site</a>
    </h4>

    @endif
    <style>
        .apple-success {
            background: #00b312;
            /* rouge très pâle — premium */
            color: #fff;
            /* rouge profond élégant */
            padding: 8px 10px;
            border-radius: 12px;
            font-size: 0.92rem;
            text-align: center;
            font-weight: 500;
            backdrop-filter: blur(4px);
        }
    </style>
</x-guest-layout>