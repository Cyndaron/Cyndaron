"use strict";

$(document).ready(function()
{
    $('#gcv-add-date-save').on('click keyup', function ()
    {
        $.post({
            url: '/api/contest/addDate',
            data: $('#gcv-add-date-form').serialize(),
        }).done(function (data) {
            location.reload();
        });

    });
});