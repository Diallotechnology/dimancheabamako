@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'dimancheabamako')
            <img src="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}" width="100" alt="Logo">
            @else
            {{ $slot }}
            @endif
        </a>
    </td>
</tr>