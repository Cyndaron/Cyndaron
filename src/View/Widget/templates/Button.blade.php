@php
    if (!isset($description))
        $description = '';
    if (!isset($text))
        $text = null;
    if (!isset($size))
        $size = 20;

    $btnClass = \Cyndaron\View\Template\ViewHelpers::getButtonClass($kind);

    if ($size === 16)
    {
        $btnClass .= ' btn-sm';
    }

    $textAfterIcon = $text ? " $text" : '';
@endphp

<a class="btn {{ $btnClass }}" href="{{ $link }}" @if($description)title="{{ $description }}"@endif>
    @include('View/Widget/Icon', ['type' => $kind]){{ $textAfterIcon }}
</a>
