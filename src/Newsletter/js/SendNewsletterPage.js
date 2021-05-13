'use strict';

$(document).ready(function ()
{
    $('#send-newsletter').on('click', function ()
    {
        let subject = $('#subject').val();
        if (!subject)
        {
            alert('Er is geen onderwerp opgegeven!');
            return;
        }

        CKEDITOR.instances['ckeditor-parent'].updateElement();

        let body = $('#ckeditor-parent').val();
        if (body === '')
        {
            alert('Er is geen tekst ingevoerd!');
            return;
        }

        let recipient = $("input[name='recipient']:checked").val();
        if (!recipient)
        {
            alert('Er is geen ontvanger opgegeven!');
            return;
        }

        let recipientAddress = $('#recipient-address').val();
        if (recipient === 'single' && !recipientAddress)
        {
            alert('U moet een adres opgeven!');
            return;
        }

        let csrfToken = $(this).data('csrf-token');
        $.post(
            '/newsletter/send',
            { subject: subject, body: body, recipient: recipient, recipientAddress: recipientAddress, csrfToken: csrfToken }, null, 'json')
            .done(function ()
            {
                alert('Nieuwsbrief is verstuurd!');
            })
            .fail(function (xhr, textStatus, errorThrown)
            {
                alert('Nieuwsbrief versturen mislukt: ' + xhr.responseJSON.error);
            });
    });
});
