<div class="form-group row">
    <label @if (isset($id)) for="{{ $id }}" @endif class="col-md-3 col-form-label">@if (isset($label)){!! $label !!}:@endif</label>
    <div class="col-md-6">
        {!! $right ?? '' !!}
    </div>
</div>