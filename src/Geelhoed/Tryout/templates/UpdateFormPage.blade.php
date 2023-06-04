@extends('Index')

@section('contents')
    <form method="post" enctype="multipart/form-data">
        @include('View/Widget/Form/File', ['id' => 'datatot', 'label' => 'Nieuwe database', 'accept' => '.mdb'])
        <input type="hidden" name="csrfToken" value="{{ $csrfToken }}"/>
        @component('View/Widget/Form/FormWrapper')
            @slot('right')
                <button type="submit" class="btn btn-primary">Uploaden</button>
            @endslot
        @endcomponent
    </form>
@endsection
