@include('View/Widget/Form/BasicInput', ['id' => 'titel', 'required' => true, 'value' => $contentTitle, 'label' => $t->get('Titel')])

@if ($hasCategory)
    @include ('View/Widget/Form/Checkbox', ['id' => 'showBreadcrumbs', 'description' => $t->get('Titel tonen als breadcrumbs'), 'checked' => $showBreadcrumbs])
@endif

@component('View/Widget/Form/FormWrapper', ['id' => 'friendlyUrl', 'label' => $t->get('Friendly URL')])
    @slot('right')
        <div class="input-group">
            <div class="input-group-prepend">
                            <span class="input-group-text"
                                  id="basic-addon3">{{ $friendlyUrlPrefix }}</span>
            </div>
            <input type="text" class="form-control" id="friendlyUrl" name="friendlyUrl"
                   aria-describedby="basic-addon3" value="{{ $friendlyUrl }}" pattern="[a-z0-9\-\.]*"/>
        </div>
    @endslot
@endcomponent
