'use strict';

$(document).ready(function ()
{
    $('.eom-order-set-approval-status').on('click', function () {
        let orderId = $(this).data('order-id');
        let csrfToken = $(this).data('csrf-token-set-approval-status');
        let status = $(this).data('approval-status');
        $.post('/eventSbk-registration/setApprovalStatus/' + orderId, { csrfToken: csrfToken, status: status }).done(function () {
            location.reload();
        });
    });

    $('.eom-order-set-paid').on('click', function () {
        let orderId = $(this).data('order-id');
        let csrfToken = $(this).data('csrf-token-set-is-paid');
        $.post('/eventSbk-registration/setIsPaid/' + orderId, { csrfToken: csrfToken }).done(function () {
            location.reload();
        });
    });

    $('.eom-order-delete').on('click', function ()
    {
        let orderId = $(this).data('order-id');
        let csrfToken = $(this).data('csrf-token-delete');
        del(`Weet u zeker dat u deze aanmelding wilt verwijderen?`, function() {
            $.post('/eventSbk-registration/delete/' + orderId, { csrfToken: csrfToken }).done(function () {
                location.reload();
            });
        });
    });
});
