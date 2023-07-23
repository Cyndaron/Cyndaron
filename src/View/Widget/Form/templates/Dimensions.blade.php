@component('View/Widget/Form/FormWrapper', ['id' => '', 'label' => $label])
    @slot('right')
        <div class="input-group widget-dimensions">
            <input id="{{ $id1 }}" name="{{ $name1 ?? $id1 }}" type="number" class="form-control" step="1" value="{{ $value1 }}">
            <span class="input-group-text">×</span>
            <input id="{{ $id2 }}" name="{{ $name2 ?? $id2 }}" type="number" class="form-control" step="1" value="{{ $value2 }}">
        </div>
    @endslot
@endcomponent
