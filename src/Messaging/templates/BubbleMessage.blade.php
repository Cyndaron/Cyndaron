@php
    $isMe ??= false;

    $class = 'bubble-message';
    $class .= $isMe ? ' bubble-right' : ' bubble-left';
    $class .= $isRead ? '' : ' bubble-unread';
@endphp

<div class="{{ $class }}">

    @if (isset($profileLink))
        <a href="{!! $profileLink !!}">
            <img class="bubble-avatar" src="{{ $profileAvatar }}" alt="">
        </a>
    @elseif ($profileAvatar)
        <img class="bubble-avatar" src="{{ $profileAvatar }}" alt="">
    @endif

    <div class="berichtboven">
        <div class="berichtstatusbox-bubbel"><span>Verstuurd: {{ $dateTime|dmyHm }}</span>
            @if (!$isRead)
                &nbsp; <span class="glyphicon glyphicon-eye-close" title="Dit bericht is nog niet gelezen door de ontvanger"></span>
            @endif
        </div>

        @if (isset($buttons))
            <div class="btn-group-xs" role="group">
                {!! $buttons !!}
            </div>
        @endif
    </div>
    {!! $message !!}
</div>
