"use strict";

$(document).ready(function() {
    $('.gcsm-delete').on('click', function() {
        let row = $(this).parent().parent();
        let id = $(this).data('id');
        let csrfToken = $('#gcsm-table').data('csrf-token-delete');
        del('Weet u zeker dat u deze inschrijving wilt verwijderen?', function() {
            $.post('/contest/removeSubscription', { id: id, csrfToken: csrfToken })
                .done(function () {
                    row.remove();
                    $('#confirm-dangerous').modal('hide');
                });
        });
    });
});