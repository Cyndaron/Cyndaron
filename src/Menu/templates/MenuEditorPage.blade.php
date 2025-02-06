@php /** @var \Cyndaron\Url\UrlService $urlService */ @endphp
@extends ('Index')

@section ('contents')

    @component('View/Widget/Toolbar')
        @slot('right')
            <button id="mm-create-item"
                    data-csrf-token="{{ $tokenHandler->get('menu', 'addItem') }}"
                    type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#mm-edit-item-dialog">
                @include('View/Widget/Icon', ['type' => 'new']) Nieuw menuitem
            </button>
        @endslot
    @endcomponent

    <table id="mm-menutable" class="table table-striped table-bordered"
           data-edit-csrf-token="{{ $tokenHandler->get('menu', 'editItem') }}"
           data-delete-csrf-token="{{ $tokenHandler->get('menu', 'deleteItem') }}">
        <thead>
        <tr>
            <th>ID</th>
            <th>Link</th>
            <th>Titel</th>
            <th>Dropdown</th>
            <th>Afbeelding</th>
            <th>Prioriteit</th>
            <th>Acties</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($menuItems as $menuItem)
        <tr>
            <td>
                {{ $menuItem->id }}
            </td>
            <td>
                {{ $menuItem->link }}
            </td>
            <td>
                {{ $menuItem->getTitle($urlService) }}
            </td>
            <td>
                {{ $menuItem->isDropdown|boolToText }}
            </td>
            <td>
                {{ $menuItem->isImage|boolToText }}
            </td>
            <td>
                {{ $menuItem->priority }}
            </td>
            <td>
                <div class="btn-group">
                    <button class="mm-edit-item btn btn-outline-cyndaron"
                            data-id="{{ $menuItem->id }}"
                            data-bs-toggle="modal"
                            data-bs-target="#mm-edit-item-dialog"
                            data-priority="{{ $menuItem->priority }}"
                            data-link="{{ $menuItem->link }}"
                            data-alias="{{ $menuItem->alias }}"
                            data-isDropdown="{{ $menuItem->isDropdown }}"
                            data-isImage="{{ $menuItem->isImage }}"
                    >@include('View/Widget/Icon', ['type' => 'edit'])</button>
                    <button class="mm-delete-item btn btn-danger" data-id="{{ $menuItem->id }}">@include('View/Widget/Icon', ['type' => 'delete'])</button>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @component('View/Widget/Modal', ['id' => 'mm-edit-item-dialog', 'title' => 'Menu-item bewerken'])
        @slot('body')
            <input type="hidden" id="mm-id" />
            <input type="hidden" id="mm-csrf-token" />

            <div class="form-group row">
                <label for="mm-link" class="col-sm-2 col-form-label">Link:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="mm-link">
                </div>
            </div>
            <div class="form-group row">
                <label for="mm-alias" class="col-sm-2 col-form-label">Alias:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="mm-alias">
                </div>
            </div>
            <div class="form-group row">
                <label for="mm-priority" class="col-sm-2 col-form-label">Prioriteit:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="mm-priority" type="number">
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <input type="checkbox" class="" id="mm-isDropdown" value="1">
                    <label class="form-check-label" for="mm-isDropdown">Dropdown</label>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <input type="checkbox" class="" id="mm-isImage" value="1">
                    <label class="form-check-label" for="mm-isImage">Als afbeelding</label>
                </div>
            </div>
        @endslot
        @slot('footer')
            <button id="mm-edit-item-save" type="button" class="btn btn-primary">Opslaan</button>
            <button type="button" class="btn btn-outline-cyndaron" data-bs-dismiss="modal">Annuleren</button>
        @endslot
    @endcomponent

@endsection
