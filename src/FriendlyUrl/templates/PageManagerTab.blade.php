@component('View/Widget/Toolbar')
    @slot('right')
        <label for="pm-friendlyurl-new-name" class="mr-sm-2">Nieuwe friendly URL:</label>
        <input id="pm-friendlyurl-new-name" type="text" placeholder="URL" class="form-control mr-sm-2" required/>
        <input id="pm-friendlyurl-new-target" type="text" placeholder="Verwijzingsdoel" class="form-control mr-sm-2" required/>
        <button id="pm-create-friendlyurl" type="button" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('friendlyurl', 'add') }}" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Aanmaken</button>
    @endslot
@endcomponent

<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>URL</th>
            <th>Verwijzingsdoel</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\FriendlyUrl\FriendlyUrl[] $friendlyUrls */@endphp
        @foreach ($friendlyUrls as $friendlyurl)
        <tr id="pm-row-friendlyurl-{{ $friendlyurl->id }}">
            <td>{{ $friendlyurl->id }}</td>
            <td>
                <a href="/{{ $friendlyurl->name }}">{{ $friendlyurl->name }}</a>
            </td>
            <td>
                {{ $friendlyurl->target }}
            </td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="friendlyurl" data-id="{{ $friendlyurl->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('friendlyurl', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder deze friendly URL"></span></button>
                    <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="friendlyurl" data-id="{{ $friendlyurl->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('friendlyurl', 'addtomenu') }}"><span class="glyphicon glyphicon-bookmark" title="Voeg deze friendly URL toe aan het menu"></span></button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
