@component('View/Widget/Toolbar')
    @slot('right')
        <label for="pm-friendlyurl-new-name" class="mr-sm-2">Nieuwe friendly URL:</label>
        <input id="pm-friendlyurl-new-name" type="text" placeholder="URL" class="form-control form-control-inline mr-sm-2" required/>
        <input id="pm-friendlyurl-new-target" type="text" placeholder="Verwijzingsdoel" class="form-control form-control-inline mr-sm-2" required/>
        <button id="pm-create-friendlyurl" type="button" data-csrf-token="{{ $tokenAdd}}" class="btn btn-success">@include('View/Widget/Icon', ['type' => 'new']) Aanmaken</button>
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
                    <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="friendlyurl" data-id="{{ $friendlyurl->id }}" data-csrf-token="{{ $tokenDelete }}" title="Verwijder deze friendly URL">@include('View/Widget/Icon', ['type' => 'delete'])</button>
                    <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="friendlyurl" data-id="{{ $friendlyurl->id }}" data-csrf-token="{{ $tokenAddToMenu }}" title="Voeg deze friendly URL toe aan het menu">@include('View/Widget/Icon', ['type' => 'bookmark'])</button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
