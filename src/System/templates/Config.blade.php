@extends ('Index')

@section ('contents')
    @include('View/Widget/PageTabs', ['subPages' => $pageTabs, 'urlPrefix' => '/system/', 'currentPage' => $currentPage ])

    <div class="container-fluid tab-contents">
        <form method="post" action="/system/config" class="form-horizontal">
            @foreach ($formItems as $formItem)
            <div class="form-group row">
                <label for="{{ $formItem['name'] }}" class="col-md-3 col-form-label col-form-label-md">{{ $formItem['description'] }}:</label>
                <div class="col-md-6">
                    @php $class = !in_array($formItem['type'], ['checkbox', 'radio']) ? 'form-control form-control-md' : '' @endphp
                    <input type="{{ $formItem['type'] }}" class="{{ $class }}" id="{{ $formItem['name'] }}" name="{{ $formItem['name'] }}" value="{{ $formItem['value'] }}" {{ $formItem['extraAttr'] ?? '' }}/>
                </div>
            </div>
            @endforeach

            <div class="form-group row">
                <label class="col-md-3 col-form-label col-form-label-md">Standaardcategorie:</label>
                <div class="col-md-6">
                    <select name="defaultCategory" class="custom-select">
                        <option value="0" @if ($defaultCategory === 0) selected @endif>Geen</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @if ($category->id === $defaultCategory) selected @endif>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label col-form-label-md">Menuthema:</label>
                <div class="col-md-6">
                    <select id="menuTheme" name="menuTheme" class="custom-select">
                        <option value="light" {{ $lightMenu }}>Licht</option>
                        <option value="dark" {{ $darkMenu }}>Donker</option>
                    </select>
                </div>
            </div>



            <div class="form-group row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\UserSession::getCSRFToken('system', 'config') }}"/>
                    <input class="btn btn-primary" type="submit" id="cm-save" value="Opslaan"/>
                    <input class="btn btn-outline-cyndaron" type="button" id="testColors" value="Kleuren testen"/>
                </div>
            </div>
        </form>
    </div>
@endsection
