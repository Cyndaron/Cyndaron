@php
    if (!isset($description))
        $description = '';
    if (!isset($text))
        $text = null;
    if (!isset($size))
        $size = 20;

    [$icon, $btnClass] = \Cyndaron\View\Template\ViewHelpers::getButtonIconAndClass($kind);

    if ($size === 16)
    {
        $btnClass .= ' btn-sm';
    }

    $textAfterIcon = $text ? " $text" : '';
@endphp

<a class="btn {{ $btnClass }}" href="{{ $link }}" @if($description)title="{{ $description }}"@endif>
    <span class="glyphicon glyphicon-{{ $icon }}"></span>{{ $textAfterIcon }}
</a>
