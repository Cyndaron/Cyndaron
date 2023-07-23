@component('View/Widget/Form/FormWrapper', ['id' => $id, 'label' => $label])
    @slot('right')
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">€</span>
            </div>
            <input type="text" class="form-control" id="{{ $id }}" name="{{ $id }}" value="{{ $value ?? '' }}">
        </div>
    @endslot
@endcomponent
