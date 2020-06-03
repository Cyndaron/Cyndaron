@component('Widget/Toolbar')
    @slot('right')
        <label for="pm-category-new-name" class="mr-sm-2">Nieuwe categorie:</label>
        <input class="form-control mr-sm-2" id="pm-category-new-name" type="text"/>
        <button type="button" id="pm-create-category" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('category', 'add') }}" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Aanmaken</button>
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
                <td>{{ $category->id }}</td>
                <td>
                    <a href="/category/{{ $category->id }}"><b>{{ $category->name }}</b></a>
                </td>
                <td>
                    <div class="btn-group">
                        @include('Widget/Button', ['kind' => 'edit', 'link' => "/editor/category/{$category->id}", 'title' => 'Deze categorie bewerken', 'size' => 16])
                        <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="category" data-id="{{ $category->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('category', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder deze categorie"></span></button>
                        <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="category" data-id="{{ $category->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('category', 'addtomenu') }}"><span class="glyphicon glyphicon-bookmark" title="Voeg deze categorie toe aan het menu"></span></button>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>