<textarea id="{{ $id }}" class="ckeditor-parent" name="{{ $name ?? $id }}" rows="25" cols="125">{{ $value ?? '' }}</textarea>

@if (!empty($internalLinks))
    <div class="form-group row">
        <label class="col-sm-2 col-form-label" for="verwijzing">Interne link maken: </label>
        <div class="col-sm-5">
            <select class="internal-link-href form-control form-control-inline custom-select" data-target="{{ $id }}">
                @php /** @var \Cyndaron\Module\InternalLink[] $internalLinks */ @endphp
                @foreach ($internalLinks as $link)
                    <option value="{{ $link->link }}">{{ $link->name }}</option>
                @endforeach
            </select>
            <input type="button" class="internal-link-insert btn btn-outline-cyndaron" value="Invoegen" data-target="{{ $id }}"/>
        </div>
    </div>
@endif
