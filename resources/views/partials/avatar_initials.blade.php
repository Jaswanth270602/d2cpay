@php
    $name = trim((string)(Auth::user()->name ?? ''));
    $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
    if (count($parts) >= 2) {
        $a = function_exists('mb_substr') ? mb_substr($parts[0], 0, 1) : substr($parts[0], 0, 1);
        $b = function_exists('mb_substr') ? mb_substr($parts[count($parts) - 1], 0, 1) : substr($parts[count($parts) - 1], 0, 1);
        $initials = strtoupper($a . $b);
    } elseif (count($parts) === 1) {
        $w = $parts[0];
        $initials = strtoupper(function_exists('mb_substr') ? mb_substr($w, 0, min(2, mb_strlen($w))) : substr($w, 0, min(2, strlen($w))));
    } else {
        $initials = 'U';
    }
    $sizeClass = (($size ?? 'sm') === 'lg') ? 'avatar-initials-lg' : '';
    $extraClass = trim($extraClass ?? '');
@endphp
<span class="avatar-initials {{ $sizeClass }} {{ $extraClass }}" title="{{ $name ?: 'User' }}">{{ $initials }}</span>
