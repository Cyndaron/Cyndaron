'use strict';

$(document).ready(function ()
{
    $('.eom-registration-set-approval-status').on('click', function () {
        let registrationId = $(this).data('registration-id');
        let csrfToken = $(this).data('csrf-token-set-approval-status');
        let status = $(this).data('approval-status');
        $.post('/api/event-registration/setApprovalStatus/' + registrationId, { csrfToken: csrfToken, status: status }).done(function () {
            location.reload();
        });
    });

    $('.eom-registration-set-paid').on('click', function () {
        let registrationId = $(this).data('registration-id');
        let csrfToken = $(this).data('csrf-token-set-is-paid');
        $.post('/api/event-registration/setIsPaid/' + registrationId, { csrfToken: csrfToken }).done(function () {
            location.reload();
        });
    });

    $('.eom-registration-delete').on('click', function ()
    {
        let registrationId = $(this).data('registration-id');
        let csrfToken = $(this).data('csrf-token-delete');
        del(`Weet u zeker dat u deze aanmelding wilt verwijderen?`, function() {
            $.post('/api/event-registration/delete/' + registrationId, { csrfToken: csrfToken }).done(function () {
                location.reload();
            });
        });
    });
});
