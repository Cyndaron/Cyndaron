@extends ('Index')

@section ('contents')
    <form name="bewerkartikel" method="post" action="{{ $saveUrl }}" class="form-horizontal">

        @if ($hasTitle)
            <div class="form-group row">
                <label class="col-sm-2 col-form-label" for="titel">Titel: </label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" id="titel" name="titel"
                           value="{{ $contentTitle }}"/>
                </div>
            </div>

            @if ($hasCategory)
                @include ('Widget/Form/Checkbox', ['id' => 'showBreadcrumbs', 'description' => 'Titel tonen als breadcrumbs', 'checked' => $showBreadcrumbs])
            @endif

            <div class="form-group row">
                <label class="col-sm-2 col-form-label" for="friendlyUrl">Friendly URL: </label>
                <div class="col-sm-5">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"
                                  id="basic-addon3">{{ $friendlyUrlPrefix }}</span>
                        </div>
                        <input type="text" class="form-control" id="friendlyUrl" name="friendlyUrl"
                               aria-describedby="basic-addon3" value="{{ $friendlyUrl }}"/>
                    </div>
                </div>
            </div>
        @endif

        <textarea class="ckeditor" name="artikel" rows="25" cols="125">{{ $article }}</textarea>

        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="verwijzing">Interne link maken: </label>
            <div class="col-sm-5">
                <select id="verwijzing" class="form-control form-control-inline custom-select">
                    @foreach ($internalLinks as $link)
                        <option value="{{ $link['link'] }}">{{ $link['name'] }}</option>
                    @endforeach
                </select>
                <input type="button" id="plaklink" class="btn btn-outline-cyndaron" value="Invoegen"/>
            </div>
        </div>

        @if ($hasCategory)
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="categoryId">Plaats dit artikel in de categorie: </label>
            <div class="col-sm-5">
                <select id="categoryId" name="categoryId" class="form-control custom-select">
                    <option value="0">&lt;Geen categorie&gt;</option>
                    @foreach ($categories as $category)
                        @if ($articleType !== 'category' || $category->id !== $id)
                            <option value="{{ $category->id }}" @if ($categoryId === $category->id) selected @endif>{{ $category->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        {!! $contents !!}

        @yield ('contentSpecificButtons')


        <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('editor', $articleType) }}"/>
        <input type="submit" value="Opslaan" class="btn btn-primary"/>
        <a role="button" class="btn btn-outline-cyndaron" href="{{ $referrer }}">Annuleren</a>
    </form>

@endsection