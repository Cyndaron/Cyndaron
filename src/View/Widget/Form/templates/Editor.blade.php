<textarea id="{{ $id }}" class="ckeditor-parent" name="{{ $name ?? $id }}" rows="25" cols="125">{{ $value ?? '' }}</textarea>

@if (!empty($internalLinks))
    @component('View/Widget/Form/FormWrapper', ['id' => 'verwijzing', 'label' => $t->get('Interne link maken')])
        @slot('right')
            <div class="input-group">
                <select id="verwijzing" class="internal-link-href form-control form-control-inline custom-select" data-bs-target="{{ $id }}">
                    @php /** @var \Cyndaron\Util\Link[] $internalLinks */ @endphp
                    @foreach ($internalLinks as $link)
                        <option value="{{ $link->link }}">{{ $link->name }}</option>
                    @endforeach
                </select>
                <input type="button" class="internal-link-insert btn btn-outline-cyndaron" value="{{ $t->get('Invoegen') }}" data-bs-target="{{ $id }}"/>
            </div>
        @endslot
    @endcomponent
@endif
