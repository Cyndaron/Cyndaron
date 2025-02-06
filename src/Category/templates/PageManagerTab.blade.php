@component('View/Widget/Toolbar')
    @slot('right')
        <label for="pm-category-new-name" class="mr-sm-2">Nieuwe categorie:</label>
        <div class="input-group">
            <input class="form-control form-control-inline mr-sm-2" id="pm-category-new-name" type="text"/>
            <button type="button" id="pm-create-category" data-csrf-token="{{ $tokenAdd }}" class="btn btn-success">@include('View/Widget/Icon', ['type' => 'new']) Aanmaken</button>
        </div>
    @endslot
@endcomponent

<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Category\Category[] $categories */ @endphp
        @foreach ($categories as $category)
            <tr id="pm-row-category-{{ $category->id }}">
                <td><a href="/category/{{ $category->id }}">{{ $category->id }}</a></td>
                <td>
                    <a href="/category/{{ $category->id }}"><b>{{ $category->name }}</b></a>
                </td>
                <td>
                    <div class="btn-group">
                        @if ($userRepository->userHasRight($currentUser, 'category_edit'))
                            @include('View/Widget/Button', ['kind' => 'edit', 'link' => "/editor/category/{$category->id}", 'title' => 'Deze categorie bewerken', 'size' => 16])
                        @endif
                        @if ($currentUser->isAdmin())
                            <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="category" data-id="{{ $category->id }}" data-csrf-token="{{ $tokenDelete }}" title="Verwijder deze categorie">@include('View/Widget/Icon', ['type' => 'delete'])</button>
                            <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="category" data-id="{{ $category->id }}" data-csrf-token="{{ $tokenAddToMenu  }}" title="Voeg deze categorie toe aan het menu">@include('View/Widget/Icon', ['type' => 'bookmark'])</button>
                            <button class="btn btn-outline-cyndaron btn-sm pm-changeorder" data-id="{{ $category->id }}" data-bs-toggle="modal" data-bs-target="#pm-change-order" title="Verander de volgorde binnen deze categorie">@include('View/Widget/Icon', ['type' => 'sort-by-order'])</button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@component('View/Widget/Modal',  ['id' => 'pm-change-order', 'title' => 'Volgorde aanpassen', 'sizeClass' => 'modal-lg'])
    @slot('body')
        <form id="pm-change-order-form">
            <input type="hidden" name="csrfToken" value="{{ $tokenChangeOrder }}">
            <div id="pm-change-order-form-container"></div>
        </form>
    @endslot
    @slot('footer')
        <button id="pm-change-order-save" type="button" class="btn btn-primary">Opslaan</button>
        <button type="button" class="btn btn-outline-cyndaron" data-bs-dismiss="modal" data-bs-target="#pm-change-order">Annuleren</button>
    @endslot
@endcomponent
