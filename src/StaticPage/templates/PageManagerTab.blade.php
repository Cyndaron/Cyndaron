@component('Widget/Toolbar')
    @slot('right')
        @include('Widget/Button', ['kind' => 'new', 'link' => '/editor/sub', 'title' => 'Nieuwe statische pagina', 'text' => 'Nieuwe statische pagina'])
    @endslot
@endcomponent

@foreach ($subsPerCategory as $category => $subs)
    <h3 class="text-italic">{{ $category }}</h3>
    <table class="table table-striped table-bordered pm-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Naam</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subs as $id => $name)

            @php
                // TODO: Get rid of this disgrace.
                $vvsub = \Cyndaron\DBConnection::doQueryAndFetchFirstRow('SELECT * FROM sub_backups WHERE id= ?', [$id]);
                $hasLastVersion = !empty($vvsub);
            @endphp

            <tr id="pm-row-sub-{{ $id }}">
                <td>{{ $id }}</td>
                <td>
                    <span style="font-size: 15px;">
                        <a href="/sub/{{ $id }}"><b>{{ $name }}</b></a>
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        @include('Widget/Button', ['kind' => 'edit', 'link' => "/editor/sub/{$id}", 'title' => 'Bewerk deze statische pagina', 'size' => 16])
                        <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="sub" data-id="{{ $id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('sub', 'delete') }}"><span class="glyphicon glyphicon-trash"></span></button>
                        <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="sub" data-id="{{ $id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('sub', 'addtomenu') }}"><span class="glyphicon glyphicon-bookmark"></span></button>
                        @if ($hasLastVersion)
                            @include('Widget/Button', ['kind' => 'lastversion', 'link' => "/editor/sub/{$id}/previous", 'title' => 'Vorige versie terugzetten', 'size' => 16])
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
