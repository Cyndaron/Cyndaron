@extends ('Index')

@section ('contents')
    @include('Widget/PageTabs', ['subPages' => $pageTabs, 'urlPrefix' => '/system/', 'currentPage' => $currentPage ])

    <div class="container-fluid tab-contents">
        {!! $phpinfo !!}
    </div>
@endsection