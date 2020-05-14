'use strict';

$(document).ready(function ()
{
    $('.eom-order-set-paid').on('click', function () {
        let orderId = $(this).data('order-id');
        let csrfToken = $(this).data('csrf-token-set-is-paid');
        $.post('/api/event-order/setIsPaid/' + orderId, { csrfToken: csrfToken }).done(function () {
            location.reload()
        });
    });

    $('.eom-order-delete').on('click', function ()
    {
        let orderId = $(this).data('order-id');
        let csrfToken = $(this).data('csrf-token-delete');
        del(`Weet u zeker dat u deze inschrijving wilt verwijderen?`, function() {
            $.post('/api/event-order/delete/' + orderId, { csrfToken: csrfToken }).done(function () {
                location.reload()
            });
        });
    });
});
