'use strict';

$(document).ready(function () {
    $('.pm-delete').on('click', function () {

        let id = $(this).data('id');
        let type = $(this).data('type');
        let csrfToken = $(this).data('csrf-token');

        $('#confirm-dangerous .modal-body').html('Weet u zeker dat u dit wilt verwijderen?');
        $('#confirm-dangerous-yes').off();
        $('#confirm-dangerous-yes').on('click', function()
        {
            $.post('/api/' + type + '/delete/' + id, { csrfToken: csrfToken }).done(function() {
                $('#pm-row-' + type + '-' + id).remove();
                $('#confirm-dangerous').modal('hide');
            });
        });
        $('#confirm-dangerous').modal();
    });
    $('.pm-addtomenu').on('click', function () {
        let id = $(this).data('id');
        let type = $(this).data('type');
        let csrfToken = $(this).data('csrf-token');

        $.post('/api/' + type + '/addtomenu/' + id, { csrfToken: csrfToken }).done(function() {
            location.reload();
        });
    });
});