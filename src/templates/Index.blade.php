<!DOCTYPE HTML>
<html lang="nl" class="cyndaron">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="{{ $title }}" />
    <meta name="twitter:description" content="Klik hier om verder te lezen..." />
    <meta name="apple-mobile-web-app-title" content="{{ $websiteName }}">
    <meta name="application-name" content="{{ $websiteName }}">
    <title>{{ $title }} - {{ $websiteName }}</title>

    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css?r={{ $version }}" type="text/css" rel="stylesheet" />
    <link href="/contrib/Glyphicons/css/glyphicons.min.css?r={{ $version }}" type="text/css" rel="stylesheet" />
    <link href="/sys/css/lightbox.min.css?r={{ $version }}" type="text/css" rel="stylesheet" />
    <link href="/sys/css/cyndaron.min.css?r={{ $version }}" type="text/css" rel="stylesheet" />
    <link href="/user.css?r={{ $version }}" type="text/css" rel="stylesheet" />
    @foreach ($extraCss as $extraCssScript)
        <link href="{{ $extraCssScript }}?r={{ $version }}" type="text/css" rel="stylesheet" />
    @endforeach
    @if ($favicon)
        <link rel="icon" type="{{ $faviconType }}" href="{{ $favicon }}">
    @endif
    <style type="text/css">
        @if ($backgroundColor)
            body.cyndaron, .lightboxOverlay { background-color: {{ $backgroundColor }}; }
        @endif
        @if ($menuColor)
            .menu { background-color: {{ $menuColor }}; }
        @endif
        @if ($menuBackground)
            .menu { background-image: url('{{ $menuBackground }}'); }
        @endif
        @if ($articleColor)
            .main-content { background-color: {{ $articleColor }}; }
        @endif
        @if ($accentColor)
            a { color: {{ $accentColor }}; }
            .btn-primary { background-color: {{ $accentColor }}; border-color: {{ $accentColor }}; }
            .dropdown-item.active, .dropdown-item:active { background-color: {{ $accentColor }}; }
        @endif
    </style>
    @foreach ($preloadLinks ?? [] as $preloadLink)
        <link rel="prefetch" href="{!! $preloadLink !!}"/>
    @endforeach
    {!! $extraHead !!}
</head>
<body class="cyndaron {{ \Cyndaron\Util::slug($title) }}" data-articleColor="{{ $articleColor }}">
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

    <div id="confirm-dangerous" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Weet u het zeker?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Sluiten">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button id="confirm-dangerous-no" type="button" class="btn btn-outline-cyndaron" data-dismiss="modal">Annuleren</button>
                    <button id="confirm-dangerous-yes" type="button" class="btn btn-danger">Verwijderen</button>
                </div>
            </div>
        </div>
    </div>

    @foreach ($scripts as $script)
        <script type="text/javascript" src="{{ $script }}" nonce="{{ \Cyndaron\Router::getScriptNonce() }}"></script>
    @endforeach

    {!! $extraBodyEnd !!}
    </body>
</html>