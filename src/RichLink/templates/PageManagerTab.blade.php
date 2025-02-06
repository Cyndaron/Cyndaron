@component('View/Widget/Toolbar')
    @slot('right')
        <button id="pm-new" class="btn btn-success" type="button">@include('View/Widget/Icon', ['type' => 'new']) Nieuwe speciale link</button>
    @endslot
@endcomponent

<table id="pm-table" class="table table-striped table-bordered pm-table"
       data-csrf-token-edit="{{ $tokenEdit }}"
       data-csrf-token-delete="{{ $tokenDelete }}">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Verwijsdoel</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($richlinks as $richlink)
            <tr>
                <td>{{ $richlink->id }}</td>
                <td>{{ $richlink->name }}</td>
                <td>{{ $richlink->url }}</td>
                <td>
                    <div class="btn-group">
                        <button title="Speciale link bewerken" class="btn btn-warning pm-edit" type="button"
                                data-id="{{ $richlink->id }}"
                                data-name="{{ $richlink->name }}"
                                data-url="{{ $richlink->url }}"
                                data-blurb="{{ $richlink->blurb }}"
                                data-preview-image="{{ $richlink->previewImage }}"
                                data-open-in-new-tab="{{ (int)$richlink->openInNewTab }}"
                                data-categories="{{ implode(',', $richlink->getLinkedCategoryIds()()) }}">
                            @include('View/Widget/Icon', ['type' => 'edit'])</button>
                        <button title="Speciale link verwijderen" class="btn btn-danger pm-delete" data-id="{{ $richlink->id }}" type="button">@include('View/Widget/Icon', ['type' => 'delete'])</button>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@component('View/Widget/Modal', ['id' => 'pm-edit-dialog', 'title' => 'Speciale link toevoegen/bewerken', 'sizeClass' => 'modal-lg'])
    @slot('body')
        <input type="hidden" id="pm-edit-id" value="">

        @include('View/Widget/Form/BasicInput', ['id' => 'pm-edit-name', 'label' => 'Naam', 'required' => true])
        @include('View/Widget/Form/BasicInput', ['id' => 'pm-edit-url', 'label' => 'Verwijsdoel', 'required' => true])
        @include('View/Widget/Form/BasicInput', ['id' => 'pm-edit-previewImage', 'label' => 'Preview-afbeelding'])
        @include('View/Widget/Form/Textarea', ['id' => 'pm-edit-blurb', 'label' => 'Korte samenvatting'])
        @include('View/Widget/Form/Checkbox', ['id' => 'pm-edit-openInNewTab', 'label' => 'In nieuwe tab openen'])

        <div class="form-group row" id="categories-accordion">
            <div class="container">
                <div class="card">
                    <div class="card-header" id="heading2">
                        <h5 class="mb-0">
                            <button type="button" class="btn btn-link" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2" style="width: 100%; text-align: left;">
                                Categorieën (klik om open te klappen)
                            </button>
                        </h5>
                    </div>
                    <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#categories-accordion">
                        <div class="card-body">
                            @foreach($categories as $category)
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input category-select" id="category-{{ $category->id }}" data-category-id="{{ $category->id }}" value="1">
                                    <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endslot
    @slot('footer')
        <button type="button" class="btn btn-success" id="pm-edit-save">Opslaan</button>
        <button type="button" class="btn btn-outline-cyndaron" data-bs-toggle="modal" data-bs-target="#pm-edit-dialog">Annuleren</button>
    @endslot
@endcomponent
