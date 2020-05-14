"use strict";

function showContestEditDialog(id, name, location, sportId, date, time, deadlineDate, deadlineTime, price)
{
    $('#gcm-edit-dialog').modal().show();

    $('#gcm-edit-id').val(id);
    $('#gcm-edit-name').val(name);
    $('#gcm-edit-location').val(location);
    $('#gcm-edit-sportId option').prop('selected', false);
    $('#gcm-edit-sportId option[value=' + (sportId ? sportId : 1) + ']').prop('selected', true);
    $('#gcm-edit-date').val(date);
    $('#gcm-edit-time').val(time);
    $('#gcm-edit-deadline-date').val(deadlineDate);
    $('#gcm-edit-deadline-time').val(deadlineTime);
    $('#gcm-edit-price').val(price);
}

$(document).ready(function()
{
    $('#gcm-new').on('click', function ()
    {
        showContestEditDialog(null, null, null, null, null, null, null, null, null);
    });

    $('.gcm-edit').on('click', function()
    {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let location = $(this).data('location');
        let sportId = $(this).data('sport-id');
        let date = $(this).data('date');
        let time = $(this).data('time');
        let deadlineDate = $(this).data('deadlineDate');
        let deadlineTime = $(this).data('deadlineTime');
        let price = $(this).data('price');

        showContestEditDialog(id, name, location, sportId, date, time, deadlineDate, deadlineTime, price);
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
            location: $('#gcm-edit-location').val(),
            sportId: $('#gcm-edit-sportId').val(),
            date: $('#gcm-edit-date').val() + ' ' + $('#gcm-edit-time').val() + ':00',
            registrationDeadline: $('#gcm-edit-deadline-date').val() + ' ' + $('#gcm-edit-deadline-time').val() + ':00',
            price: parseFloat($('#gcm-edit-price').val()),
        };

        $.post('/api/contest/edit', payLoad).done(function () {
            location.reload();
        });
    });
});
