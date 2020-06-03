'use strict';

$(document).ready(function () {
    $('#pm-create-friendlyurl').on('click', function () {
        let csrfToken = $(this).data('csrf-token');
        let data = {
            name: $('#pm-friendlyurl-new-name').val(),
            target:   $('#pm-friendlyurl-new-target').val(),
            csrfToken: csrfToken
        };
        $.post('/api/friendlyurl/add', data, null, 'json').done(function() {
            location.reload();
        }).fail(function(result) {
            alert('Kon de friendly URL niet toevoegen! Melding: ' + result.responseJSON.error);
        });
    });
});