@php /** @var \Cyndaron\Url\UrlService $urlService */ @endphp
<nav class="menu navbar navbar-expand-md {{ $inverseClass }}">
    <a class="navbar-brand" href="/">{!! $navbar !!}</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Navigation omschakelen">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            @php /** @var \Cyndaron\Menu\MenuItem[] $menuItems */ @endphp
            @foreach ($menuItems as $menuitem)
                @if ($menuitem->isCategoryDropdown())
                    @include('View/Widget/MenuDropdown', ['title' => $menuitem->getTitle($urlService), 'icon' => '', 'items' => $menuitem->getSubmenu(), 'urlService' => $urlService])
                @else
                    <li class="nav-item @if ($urlService->isCurrentPage($menuitem->getLink()))active @endif @if ($menuitem->isImage) nav-item-image @endif">
                        @if ($menuitem->isImage)
                            <a class="nav-link img-in-menuitem" href="{{ $urlService->toFriendly($menuitem->getLink()) }}" target="_blank">
                                <img src="{{ $menuitem->getTitle($urlService) }}" alt="{{ $urlService->toFriendly($menuitem->getLink()) }}"/>
                            </a>
                        @else
                            <a class="nav-link" href="{{ $urlService->toFriendly($menuitem->getLink()) }}">{{ $menuitem->getTitle($urlService) }}</a>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
        <ul class="nav navbar-nav navbar-right">
            @if ($isLoggedIn)
                @if ($isAdmin)
                    <li class="nav-item">
                        <a class="nav-link" title="{{ $t->get('Nieuwe statische pagina aanmaken') }}" href="/editor/sub"><span
                                    class="glyphicon glyphicon-plus"></span></a>
                    </li>
                    @include('View/Widget/MenuDropdown', ['title' => '', 'icon' => 'wrench', 'items' => $configMenuItems])
                @endif
                @include('View/Widget/MenuDropdown', ['title' => '', 'icon' => 'user', 'items' => $userMenuItems])
            @else
                <li class="nav-item">
                    <a class="nav-link" title="{{ $t->get('Inloggen') }}" href="/user/login"><span class="glyphicon glyphicon-lock"></span></a>
                </li>
            @endif
        </ul>
    </div>
</nav>

@if (!empty($notifications))
<div class="meldingencontainer">
    <div class="meldingen alert alert-info">
        <ul>
            @foreach ($notifications as $type => $notificationsPerType)
                @foreach ($notificationsPerType as $notification)
                    <li>{{ $notification }}</li>
                @endforeach
            @endforeach
        </ul>
    </div>
</div>
@endif
