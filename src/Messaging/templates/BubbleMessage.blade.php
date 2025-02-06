@php
    /** @var \Cyndaron\Messaging\MessageInterface $message */
    $isMe ??= false;

    $class = 'bubble-message';
    $class .= $isMe ? ' bubble-right' : ' bubble-left';
    $class .= $message->isRead() ? '' : ' bubble-unread';
@endphp

<div class="{{ $class }}">

    @if ($message->getUserLink())
        <a href="{!! $message->getUserLink() !!}">
            <img class="bubble-avatar" src="{{ $message->getUserAvatar() }}" alt="">
        </a>
    @elseif ($message->getUserAvatar())
        <img class="bubble-avatar" src="{{ $message->getUserAvatar() }}" alt="">
    @endif

    <div class="berichtboven">
        <div class="berichtstatusbox-bubbel">
            <span>Verstuurd: @if ($message->getDateTime()) {{ $message->getDateTime()|dmyHm }} @else onbekend @endif</span>
            @if (!$message->isRead())
                &nbsp; @include('View/Widget/Icon', ['type' => 'eye-close', 'title' => 'Dit bericht is nog niet gelezen door de ontvanger'])
            @endif
        </div>

        @if (isset($buttons))
            <div class="btn-group-xs" role="group">
                {!! $buttons !!}
            </div>
        @endif
    </div>
    {!! $message->getBody() !!}
</div>
