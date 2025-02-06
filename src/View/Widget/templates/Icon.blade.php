@php $icon = \Cyndaron\View\Template\ViewHelpers::getIcon($type); @endphp

<span class="glyphicon glyphicon-{{ $icon }}" @if(isset($title)) title="{{ $title }}" @endif></span>
