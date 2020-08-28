"use strict";

$(document).ready(function()
{
    $('#memberId').on('change', function ()
    {
        let option = $('#memberId option:selected');
        let graduation = option.data('highest-graduation');
        $('#graduationId').val(graduation);
    })
    $('#gcv-add-date-save').on('click keyup', function ()
    {
        $.post({
            url: '/api/contest/addDate',
            data: $('#gcv-add-date-form').serialize(),
        }).done(function (data) {
            location.reload();
        }).fail(function (data) {
            let error = 'Onbekende fout.';
            if (data.hasOwnProperty('error'))
            {
                error = data.error;
            }

            alert(error);
        });
    });
});