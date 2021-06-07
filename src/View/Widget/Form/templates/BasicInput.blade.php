@component('View/Widget/Form/FormWrapper', ['id' => $id, 'label' => $label])
    @slot('right')
        <input
                type="{{ $inputType ?? $type ?? 'text' }}"
                class="form-control"
                id="{{ $id }}"
                name="{{ $id }}"
                value="{{ $value ?? '' }}"
                @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif
                @if (isset($min)) min="{{ $min }}" @endif
                @if (isset($max)) max="{{ $max }}" @endif
                @if (isset($step)) step="{{ $step }}" @endif
                @if (isset($pattern)) pattern="{{ $pattern }}" @endif
                @if (isset($datalist)) list="{{ $datalist }}" @endif
                @if (!empty($required)) required @endif>
    @endslot
@endcomponent
