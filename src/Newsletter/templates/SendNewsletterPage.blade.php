@extends('Index')

@section('contents')
    <h2>Opstellen</h2>

    @include('View/Widget/Form/BasicInput', ['id' => 'subject', 'label' => 'Onderwerp', 'value' => ''])

    @include('View/Widget/Form/Editor', ['id' => 'newsletter-body', 'name' => 'body'])

    <label for="attachment-1">Bijlagen:</label><input id="attachments" name="attachments" type="file" multiple>

    <h2>Ontvangers</h2>

    <div class="form-check">
        <input class="form-check-input" type="radio" name="recipient" id="recipient-single" value="single">
        <label class="form-check-label" for="recipient-single">
            Eén ontvanger:
        </label>
        <input type="email" id="recipient-address" name="recipient-address">
    </div>

    <div class="form-check">
        <input class="form-check-input" type="radio" name="recipient" id="recipient-subscribers" value="subscribers">
        <label class="form-check-label" for="recipient-subscribers">
            Nieuwsbriefabonnees
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="radio" name="recipient" id="recipient-everyone" value="everyone">
        <label class="form-check-label" for="recipient-everyone">
            Alle leden en nieuwsbriefabonnees
        </label>
    </div>

    <button class="btn btn-lg btn-primary" type="button" id="send-newsletter" data-csrf-token="{{ $csrfToken }}">Versturen</button>
@endsection
