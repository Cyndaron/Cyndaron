@extends('Index')

@section('contents')
    <p>
        This page allows you to test whether your server can be reached by others. Fill in the data and click ‘Check’.
    </p>
    <p>
        Your current IP is: <b>{{ $currentIP }}</b>
    </p>
    <form method="post">
        @include('View/Widget/Form/BasicInput', ['id' => 'ip', 'label' => 'IP', 'required' => true, 'placeholder' => '', 'value' => $currentIP])
        @include('View/Widget/Form/Number', ['id' => 'port', 'label' => 'Port', 'required' => true, 'placeholder' => 'Default: 11753', 'value' => '11753', 'min' => 1, 'max' => 65535, 'step' => 1, 'pattern' => '[0-9]+'])
        <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('servertest', '') }}"/>

        @component('View/Widget/Form/FormWrapper', [])
            @slot('right')
                <input type="submit" class="btn btn-primary" value="Check"/>
            @endslot
        @endcomponent
    </form>
@endsection
