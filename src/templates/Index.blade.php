<!DOCTYPE HTML>
<html lang="{{ $systemLanguage ?? 'nl' }}" class="cyndaron">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:title" content="{{ $title }}"/>
    @if ($twitterDescription)
        <meta name="twitter:description" content="{{ $twitterDescription }}"/>
    @endif
    @if ($previewImage)
        <meta name="twitter:image" content="{{ $previewImage }}"/>
    @endif
    <meta name="apple-mobile-web-app-title" content="{{ $websiteName }}">
    <meta name="application-name" content="{{ $websiteName }}">
    <title>{{ $title }} - {{ $websiteName }}</title>

    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap-reboot.min.css?r={{ $version }}" type="text/css"
          rel="stylesheet"/>
    <!-- /vendor/twbs/bootstrap/dist/css/bootstrap-reboot.min.css.map -->
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css?r={{ $version }}" type="text/css" rel="stylesheet"/>
    <!-- /vendor/twbs/bootstrap/dist/css/bootstrap.min.css.map -->
    <link href="/contrib/Glyphicons/css/glyphicons.min.css?r={{ $version }}" type="text/css" rel="stylesheet"/>
    <link href="/css/lightbox.min.css?r={{ $version }}" type="text/css" rel="stylesheet"/>
    <link href="/css/cyndaron.min.css?r={{ $version }}" type="text/css" rel="stylesheet"/>
    <link href="/user.css?r={{ $version }}" type="text/css" rel="stylesheet"/>
    @foreach ($extraCss as $extraCssScript)
        <link href="{{ $extraCssScript }}?r={{ $version }}" type="text/css" rel="stylesheet"/>
    @endforeach
    @if (!empty($favicon))
        <link rel="icon" type="{{ $faviconType }}" href="{{ $favicon }}">
    @endif
    <style type="text/css">
        @if ($backgroundColor)
            body.cyndaron, .lightboxOverlay
        {
            background-color: {{ $backgroundColor }};
        }

        @endif
        @if ($menuColor)
            .menu
        {
            background-color: {{ $menuColor }};
        }

        @endif
        @if ($menuBackground)
            .menu
        {
            background-image: url('{{ $menuBackground }}');
        }

        @endif
        @if ($articleColor)
            .main-content
        {
            background-color: {{ $articleColor }};
        }

        @endif
        @if ($accentColor)
            a
        {
            color: {{ $accentColor }};
        }

        .btn-primary
        {
            background-color: {{ $accentColor }};
            border-color: {{ $accentColor }};
        }

        .dropdown-item.active, .dropdown-item:active
        {
            background-color: {{ $accentColor }};
        }
        @endif
    </style>
    @foreach ($preloadLinks ?? [] as $preloadLink)
        <link rel="prefetch" href="{!! $preloadLink !!}"/>
    @endforeach
    @foreach ($extraHeadLines ?? [] as $extraHeadLine)
        {!! $extraHeadLine !!}
    @endforeach
    {!! $extraHead !!}
</head>
<body class="cyndaron {{ $extraBodyClasses }} {{ \Cyndaron\Util\Util::slug($title) }}"
      data-articleColor="{{ $articleColor }}">
{!! $extraBodyStart ?? '' !!}

<header class="container menu-container">
    {!! $menu !!}
</header>

@if ($showJumbo)
    <div class="container welkom-jumbo">
        {!! $jumboContents !!}
    </div>
@endif
@if (!empty($pageImage))
    <header id="page-image">
        <img src="{{ $pageImage }}" alt="">
    </header>
@endif

<main class="container main-content-container">
    <article class="main-content">
        <div class="page-title {{ $pageCaptionClasses }}">
            <h1>{!! $pageCaption !!}</h1>
            @if ($isAdmin)
                <div class="btn-group" style="vertical-align: bottom; margin-bottom: 3px;">
                    @section ('titleControls')@show
                </div>
            @endif
        </div>

        @section ('contents')
            {!! $contents !!}
        @show

    </article>
</main>

@component('View/Widget/Modal', ['id' => 'confirm-dangerous', 'title' => 'Weet u het zeker?'])
    @slot('body')

    @endslot
    @slot('footer')
        <button id="confirm-dangerous-no" type="button" class="btn btn-outline-cyndaron" data-bs-dismiss="modal">
            Annuleren
        </button>
        <button id="confirm-dangerous-yes" type="button" class="btn btn-danger">Verwijderen</button>
    @endslot
@endcomponent

@foreach ($scripts as $script)
    <script type="text/javascript" src="{{ $script }}?r={{ \Cyndaron\CyndaronInfo::ENGINE_VERSION }}"
            nonce="{{ \Cyndaron\Routing\Kernel::getScriptNonce() }}"></script>
@endforeach

{!! $extraBodyEnd !!}
</body>
</html>
