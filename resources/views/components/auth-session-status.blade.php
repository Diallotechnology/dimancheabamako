@props(['status'])

@if ($status)
    <p role="status" {{ $attributes->merge(['class' => 'rounded-lg bg-success/10 px-3 py-2 text-sm font-medium text-success']) }}>
        {{ $status }}
    </p>
@endif
