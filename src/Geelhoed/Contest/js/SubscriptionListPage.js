"use strict";

$(document).ready(function() {
    $('.gcsm-delete').on('click', function() {
        let row = $(this).parent().parent().parent();
        let id = $(this).data('id');
        let csrfToken = $('#gcsm-table').data('csrf-token-delete');
        del('Weet u zeker dat u deze inschrijving wilt verwijderen?', function() {
            $.post('/api/contest/removeSubscription', { id: id, csrfToken: csrfToken })
                .done(function () {
                    row.remove();
                    $('#confirm-dangerous').modal('hide');
                });
        });
    });

    $('.gcsm-update-payment-status').on('click', function() {
        let id = $(this).data('id');
        let isPaid = $(this).data('is-paid');
        let csrfToken = $('#gcsm-table').data('csrf-token-update-payment-status');

        $.post('/api/contest/updatePaymentStatus', { id: id, isPaid: isPaid, csrfToken: csrfToken })
            .done(function () {
                location.reload();
            });
    });
});