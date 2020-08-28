'use strict';



$(document).ready(function()
{
    $('#gpm-edit-save').on('click', function ()
    {
        let payload = {
            csrfToken: $('#gpm-edit-csrfToken').val(),
            id: $('#gpm-edit-id').val(),
            firstName: $('#gpm-edit-firstName').val(),
            initials: $('#gpm-edit-initials').val(),
            tussenvoegsel: $('#gpm-edit-tussenvoegsel').val(),
            lastName: $('#gpm-edit-lastName').val(),
            email: $('#gpm-edit-email').val(),
            sendIntroductionMail: $('#gpm-edit-sendIntroductionMail').is(':checked') ? 1 : 0,
        }

        $.post({
            url: '/api/contest/createParentAccount',
            data: payload,
            dataType: 'json'
        }).done(function() {
            location.reload();
        }).fail(function(xhr, textStatus, errorThrown) {
            let error = 'Onbekende fout.';
            let responseText = JSON.parse(xhr.responseText);
            if (responseText.hasOwnProperty('error'))
            {
                error = responseText.error;
            }

            alert(error);
        });
    });

    $('.gpm-delete-parent-account').on('click', function () {
        let element = $(this);

        del('Weet u zeker dat u deze ouderaccount wilt verwijderen?', function()
        {
            $.post({
                url: '/api/contest/deleteParentAccount/' + element.data('id'),
                data: { csrfToken: element.data('csrf-token') },
            }).done(function() {
                location.reload();
            }).fail(function(xhr, textStatus, errorThrown) {
                let error = 'Onbekende fout.';
                let responseText = JSON.parse(xhr.responseText);
                if (responseText.hasOwnProperty('error'))
                {
                    error = responseText.error;
                }

                alert(error);
            });
        })
    });

    $('.gpm-delete-from-parent-account').on('click', function() {
        let payload = {
            userId: $(this).data('user-id'),
            memberId: $(this).data('member-id'),
            csrfToken: $(this).data('csrf-token')
        };

        $.post({
            url: '/api/contest/deleteFromParentAccount',
            data: payload,
        }).done(function() {
            location.reload();
        }).fail(function(xhr, textStatus, errorThrown) {
            let error = 'Onbekende fout.';
            let responseText = JSON.parse(xhr.responseText);
            if (responseText.hasOwnProperty('error'))
            {
                error = responseText.error;
            }

            alert(error);
        });
    });
});