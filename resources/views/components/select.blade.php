@props(['name'=>'','label'=>'','required' =>true,'disabled' => false])
<label {{ $attributes->merge(['class' => 'form-label']) }}>{{ $label }}</label>

<select name="{{ $name }}" {{ $attributes->merge(['class' => 'form-select select2']) }}
    value="" @required($required) {{
    $disabled ? 'disabled' : '' }} >
    <option selected disabled value="">Selectionner</option>
    {{ $slot }}
</select>
<ul {{ $attributes->merge(['class' => 'text-sm text-danger space-y-1']) }}>
    @error($name)
    <li>{{ $message }}</li>
    @enderror
</ul>