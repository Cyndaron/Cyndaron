@extends ('Index')

@section ('contents')
    @include('View/Widget/PageTabs', ['subPages' => $pageTabs, 'urlPrefix' => '/system/', 'currentPage' => $currentPage ])

    <div class="container-fluid tab-contents">
        <h3>Maprechten</h3>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Map/bestand</th>
                    <th>Verwachting</th>
                    <th>Resultaat</th>
                </tr>
            </thead>
            <tbody>
                @php /** @var \Cyndaron\System\ExpectedResult[] $folderResults */ @endphp
                @foreach ($folderResults as $folder => $result)
                    <tr>
                        <td>{{ $folder }}</td>
                        <td>{{ $result->expected }}</td>
                        <td>{{ $result->result ? 'OK' : 'Incorrect' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Instellingen</h3>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Instelling</th>
                    <th>Verwachting</th>
                    <th>Waarde</th>
                </tr>
            </thead>
            <tbody>
                @php /** @var \Cyndaron\System\ExpectedResult[] $settings */ @endphp
                @foreach ($settings as $setting => $definition)
                    <tr>
                        <td>{{ $setting }}</td>
                        <td>{{ $definition->expected }}</td>
                        <td>{{ $definition->result }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
