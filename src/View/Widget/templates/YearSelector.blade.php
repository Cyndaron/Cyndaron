@if (!empty($showEmpty))
    <option value="">&nbsp;</option>
@endif
@php $laatsteWaarde = ($selectedYear >= 1900) ? min($selectedYear, date('Y') - 36) : date("Y") - 36; @endphp

@for ($i = date('Y') - 5; $i >= $laatsteWaarde; $i--)
    @php $sel = ($i === $selectedYear) ? "selected" : "" @endphp
    <option value="{{ $i }}" {{ $sel }}>{{ $i }}</option>
@endfor