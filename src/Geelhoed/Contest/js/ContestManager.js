"use strict";

function showContestEditDialog(id, name, description, location, sportId, deadlineDate, deadlineTime, registrationChangeDeadlineDate, registrationChangeDeadlineTime, price)
{
    $('#gcm-edit-dialog').modal().show();

    $('#gcm-edit-id').val(id);
    $('#gcm-edit-name').val(name);
    $('#gcm-edit-description').val(description);
    $('#gcm-edit-location').val(location);
    $('#gcm-edit-sportId option').prop('selected', false);
    $('#gcm-edit-sportId option[value=' + (sportId ? sportId : 1) + ']').prop('selected', true);

    $('#gcm-edit-deadline-date').val(deadlineDate);
    $('#gcm-edit-deadline-time').val(deadlineTime);
    $('#gcm-edit-registration-change-deadline-date').val(registrationChangeDeadlineDate);
    $('#gcm-edit-registration-change-deadline-time').val(registrationChangeDeadlineTime);
    $('#gcm-edit-price').val(price);
}

$(document).ready(function()
{
    $('#gcm-new').on('click', function ()
    {
        showContestEditDialog(null, null, null, null, null, null, null, null, null, null);
    });

    $('.gcm-edit').on('click', function()
    {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let description = $(this).data('description');
        let location = $(this).data('location');
        let sportId = $(this).data('sport-id');
        let deadlineDate = $(this).data('deadline-date');
        let deadlineTime = $(this).data('deadline-time');
        let registrationChangeDeadlineDate = $(this).data('registration-change-deadline-date');
        let registrationChangeDeadlineTime = $(this).data('registration-change-deadline-time');
        let price = $(this).data('price');

        showContestEditDialog(id, name, description, location, sportId, deadlineDate, deadlineTime, registrationChangeDeadlineDate, registrationChangeDeadlineTime, price);
    });

    $('.gcm-delete').on('click', function ()
    {
        let id = $(this).data('id');
        let csrfToken = $('#gcm-table').data('csrf-token-delete');
        let row = $(this).parent().parent().parent();

        del('Weet u zeker dat u deze wedstrijd wilt verwijderen?', function()
        {
            $.post('/api/contest/delete', { id: id, csrfToken: csrfToken }).done(function()
            {
                row.remove();
                $('#confirm-dangerous').modal('hide');
            });
        });
    });

    $('#gcm-edit-save').on('click', function ()
    {
        let payLoad = {
            csrfToken: $('#gcm-table').data('csrf-token-edit'),
            id: $('#gcm-edit-id').val(),
            name: $('#gcm-edit-name').val(),
            description: $('#gcm-edit-description').val(),
            location: $('#gcm-edit-location').val(),
            sportId: $('#gcm-edit-sportId').val(),
            registrationDeadline: $('#gcm-edit-deadline-date').val() + ' ' + $('#gcm-edit-deadline-time').val() + ':00',
            registrationChangeDeadline: $('#gcm-edit-registration-change-deadline-date').val() + ' ' + $('#gcm-edit-registration-change-deadline-time').val() + ':00',
            price: parseFloat($('#gcm-edit-price').val()),
        };

        $.post('/api/contest/edit', payLoad).done(function () {
            location.reload();
        });
    });
});
