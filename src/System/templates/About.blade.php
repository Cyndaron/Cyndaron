@extends ('Index')

@section ('contents')
    @include('Widget/PageTabs', ['subPages' => $pageTabs, 'urlPrefix' => '/system/', 'currentPage' => $currentPage ])

    <div class="container-fluid tab-contents">
        <h1>{{ $productName }} {{ $productVersion }} ({{ $productCodename }})</h1>
        Engineversie: {{ $engineVersion }}<br />
        © Michael Steenbeek, 2009-2019<br />
        Beschikbaar onder de ISC-licentie (zie het bestand LICENSE), m.u.v. van de volgende onderdelen:
        <ul>
            <li>Lightbox: MIT-licentie (LICENSE.Lightbox)</li>
            <li>MCServerStats: MIT-licentie (LICENSE.MCServerStats)</li>
            <li>MinecraftSkinRenderer: BSD-3-licentie (LICENSE.MinecraftSkinRenderer)</li>
        </ul>
    </div>
@endsection