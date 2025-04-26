@extends('Index')

@section('contents')
    @if (!empty($message))
        <div class="alert alert-warning">
            {{ $message }}
        </div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Key</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($results as $key => $value)
            <tr>
                <td>{{ $key }}</td>
                <td>
                    @if (is_array($value))
                        <ul>
                            @foreach ($value as $innerKey => $innerValue)
                                <li>{{ $innerKey }}: {{ $innerValue }}</li>
                            @endforeach
                        </ul>
                    @elseif (is_bool($value))
                        {{ $value ? 'Yes' : 'No' }}
                    @else
                        {{ $value }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
