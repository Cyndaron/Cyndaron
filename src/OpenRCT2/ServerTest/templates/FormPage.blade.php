@extends('Index')

@section('contents')
    <form method="post">
        @include('View/Widget/Form/BasicInput', ['id' => 'ip', 'label' => 'IP', 'required' => true, 'placeholder' => '', 'value' => ''])
        @include('View/Widget/Form/Number', ['id' => 'port', 'label' => 'Port', 'required' => true, 'placeholder' => 'Default: 11753', 'value' => '11753', 'min' => 1, 'max' => 65535, 'step' => 1, 'pattern' => '[0-9]+'])
        <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('servertest', '') }}"/>

        @component('View/Widget/Form/FormWrapper', [])
            @slot('right')
                <input type="submit" class="btn btn-primary" value="Check"/>
            @endslot
        @endcomponent
    </form>
@endsection
