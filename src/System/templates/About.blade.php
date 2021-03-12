@extends ('Index')

@section ('contents')
    @include('View/Widget/PageTabs', ['subPages' => $pageTabs, 'urlPrefix' => '/system/', 'currentPage' => $currentPage ])

    <div class="container-fluid tab-contents">
        <h1>{{ $productName }} {{ $productVersion }} ({{ $productCodename }})</h1>
        Engineversie: {{ $engineVersion }}<br />
        © Michael Steenbeek, 2009-2020<br />
        Beschikbaar onder een MIT-licentie (zie het bestand LICENSE). Code van derde partijen valt onder de volgende licenties:
        <ul>
            <li>Lightbox: MIT-licentie (LICENSE.Lightbox)</li>
            <li>MCServerStats: MIT-licentie (LICENSE.MCServerStats)</li>
            <li>MinecraftSkinRenderer: BSD-3-licentie (LICENSE.MinecraftSkinRenderer)</li>
        </ul>
    </div>
@endsection
