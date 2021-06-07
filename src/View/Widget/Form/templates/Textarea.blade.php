@component('View/Widget/Form/FormWrapper', ['id' => $id, 'label' => $label])
    @slot('right')
        <textarea class="form-control" id="{{ $id }}" name="{{ $id }}" rows="4" @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif>{{ $value ?? '' }}</textarea>
    @endslot
@endcomponent
