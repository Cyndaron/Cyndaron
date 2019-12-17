@extends('Index')

@section ('contents')
    {!! $text !!}

    @foreach ($replies as $reply)
        <article class="card mb-2">
            <div class="card-header">
                Reactie van <strong>{{ reply.author }}</strong> op <time datetime="{{ reply.created }}">{{ reply.friendlyDate }} om {{ reply.friendlyTime }}</time>
            </div>
            <div class="card-body">
                {{ reply.text }}
            </div>
        </article>
    @endforeach

    @if ($replies && !$allowReplies)
        Op dit bericht kan niet meer worden gereageerd.<br />
    @endif

    @if ($allowReplies)
        <h3>Reageren:</h3>
        <form name="reactie" method="post" action="/sub/react/{{ $id }}" class="form-horizontal">
            <div class="form-group row">
                <label for="author" class="col-sm-3 col-form-label">Naam: </label>
                <div class="col-sm-9">
                    <input id="author" name="author" maxlength="100" class="form-control"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="reactie" class="col-sm-3 col-form-label">Reactie: </label>
                <div class="col-sm-9">
                    <textarea style="height: 100px;" id="reactie" name="reactie" class="form-control"></textarea>
                </div>
            </div>
            <div class="form-group row">
                <label for="antispam" class="col-sm-3 col-form-label">Hoeveel is de wortel uit 64?: </label>
                <div class="col-sm-9">
                    <input id="antispam" name="antispam" class="form-control"/>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-offset-1 col-sm-9">
                    <input type="submit" class="btn btn-primary" value="Versturen"/>
                </div>
            </div>
            <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('sub', 'react') }}"/>
        </form>
    @endif

@endsection