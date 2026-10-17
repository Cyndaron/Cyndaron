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

            @include('View/Widget/Form/BasicInput', ['id' => 'mm-link', 'required' => true, 'label' => $t->get('Link')])
            @include('View/Widget/Form/BasicInput', ['id' => 'mm-alias', 'required' => false, 'label' => $t->get('Alias')])
            @include('View/Widget/Form/Number', ['id' => 'mm-priority', 'required' => false, 'label' => $t->get('Prioriteit')])
            @include ('View/Widget/Form/Checkbox', ['id' => 'mm-isDropdown', 'description' => $t->get('Dropdown')])
            @include ('View/Widget/Form/Checkbox', ['id' => 'mm-isImage', 'description' => $t->get('Als afbeelding')])
        @endslot
        @slot('footer')
            <button id="mm-edit-item-save" type="button" class="btn btn-primary">Opslaan</button>
            <button type="button" class="btn btn-outline-cyndaron" data-bs-dismiss="modal">Annuleren</button>
        @endslot
    @endcomponent

@endsection
