@extends ('Index')

@section ('contents')
    @include('Widget/PageTabs', ['subPages' => $pageTabs, 'urlPrefix' => '/pagemanager/', 'currentPage' => $currentPage])

    <div class="container-fluid tab-contents">
        {!! $tabContents !!}
    </div>
@endsection