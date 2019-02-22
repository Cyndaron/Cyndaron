'use strict';

$(document).ready(function ()
{
    $('.com-order-set-paid').on('click', function () {
        let orderId = $(this).data('order-id');
        let csrfToken = $(this).data('csrf-token-set-is-paid');
        $.post('/concert-order/setIsPaid/' + orderId, { csrfToken: csrfToken }).done(function () {
            location.reload()
        });
    });
    $('.com-order-set-sent').on('click', function () {
        let orderId = $(this).data('order-id');
        let csrfToken = $(this).data('csrf-token-set-is-sent');
        $.post('/concert-order/setIsSent/' + orderId, { csrfToken: csrfToken }).done(function () {
            location.reload()
        })
    });

    $('.com-order-delete').on('click', function ()
    {
        let orderId = $(this).data('order-id');
        let csrfToken = $(this).data('csrf-token-delete');
        del(`Weet u zeker dat u deze bestelling wilt verwijderen?`, function() {
            $.post('/concert-order/delete/' + orderId, { csrfToken: csrfToken }).done(function () {
                location.reload()
            });
        });
    });
});
