@extends('Index')

@section('contents')
    <form method="post">
        @include('View/Widget/Form/BasicInput', ['id' => 'ip', 'label' => 'IP', 'placeholder' => '', 'value' => ''])
        @include('View/Widget/Form/BasicInput', ['id' => 'port', 'label' => 'Port', 'placeholder' => 'Default: 11753', 'value' => '11753'])
        <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('servertest', '') }}"/>

        @component('View/Widget/Form/FormWrapper', [])
            @slot('right')
                <input type="submit" class="btn btn-primary" value="Check"/>
            @endslot
        @endcomponent
    </form>
@endsection
