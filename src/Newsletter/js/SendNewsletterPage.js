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

        CKEDITOR.instances['newsletter-body'].updateElement();

        let body = $('#newsletter-body').val();
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

        const attachments = document.getElementById('attachments').files;
        let csrfToken = $(this).data('csrf-token');

        let formData = new FormData();
        formData.append('subject', subject);
        formData.append('body', body);
        formData.append('recipient', recipient);
        formData.append('recipientAddress', recipientAddress);
        formData.append('csrfToken', csrfToken);
        for (let i = 0; i < attachments.length; i++)
        {
            formData.append('attachments[]', attachments[i]);
        }

        $.ajax({
            url : '/api/newsletter/send',
            data: formData,
            type: 'POST',
            contentType: false,
            processData: false,
        }).done(function ()
        {
            alert('Nieuwsbrief is verstuurd!');
        })
        .fail(function (xhr, textStatus, errorThrown)
        {
            alert('Nieuwsbrief versturen mislukt: ' + xhr.responseJSON.error);
        });
    });
});
