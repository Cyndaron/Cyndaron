@php
    if (!isset($description))
        $description = '';
    if (!isset($text))
        $text = null;
    if (!isset($size))
        $size = 20;
@endphp
{!! new \Cyndaron\Widget\Button($kind, $link, $description, $text, $size) !!}