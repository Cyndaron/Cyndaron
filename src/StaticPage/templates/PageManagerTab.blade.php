@component('View/Widget/Toolbar')
    @slot('right')
        @include('View/Widget/Button', ['kind' => 'new', 'link' => '/editor/sub', 'title' => $t->get('Nieuwe statische pagina'), 'text' => $t->get('Nieuwe statische pagina')])
    @endslot
@endcomponent

@foreach ($subsPerCategory as $category => $subs)
    <h3 class="text-italic">{{ $category }}</h3>
    <table class="table table-striped table-bordered pm-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ $t->get('Naam') }}</th>
                <th>{{ $t->get('Acties') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subs as $id => $sub)
            <tr id="pm-row-sub-{{ $id }}">
                <td><a href="/sub/{{ $id }}">{{ $id }}</a></td>
                <td>
                    <span style="font-size: 15px;">
                        <a href="/sub/{{ $id }}"><b>{{ $sub['name'] }}</b></a>
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        @include('View/Widget/Button', ['kind' => 'edit', 'link' => "/editor/sub/{$id}", 'title' => 'Bewerk deze statische pagina', 'size' => 16])
                        <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="sub" data-id="{{ $id }}" data-csrf-token="{{ $tokenDelete }}"><span class="glyphicon glyphicon-trash"></span></button>
                        <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="sub" data-id="{{ $id }}" data-csrf-token="{{ $tokenAddToMenu }}"><span class="glyphicon glyphicon-bookmark"></span></button>
                        @if ($sub['hasBackup'])
                            @include('View/Widget/Button', ['kind' => 'lastversion', 'link' => "/editor/sub/{$id}/previous", 'title' => 'Vorige versie terugzetten', 'size' => 16])
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
