@if (!empty($showEmpty))
    <option value="">&nbsp;</option>
@endif

@for ($i = 1; $i <= 31; $i++)
    @php
        $i = (strlen($i) === 1) ? $i = '0' . $i : $i;
        $sel = ($i === $selectedMonth) ? 'selected' : '';
    @endphp
    <option value="{{ $i }}" {{ $sel }}>{{ \Cyndaron\View\Template\ViewHelpers::getDutchMonth($i) }}</option>
@endfor
