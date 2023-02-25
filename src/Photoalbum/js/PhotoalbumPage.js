'use strict';

$(document).ready(function () {
    $('#upload-photo').on('submit', function(event)
    {
        event.preventDefault();

        $('#add-photo-submit').val('Bezig met uploaden…')

        const destination = '/api' + $(this).attr('action');
        const photos = document.getElementById('newFile').files;
        const csrfToken = $('input[name=csrfToken]').val();

        let numSucceeded = 0;
        let numFailed = 0;

        let calls = [];

        for (let i = 0; i < photos.length; i++)
        {
            let formData = new FormData();
            formData.append('csrfToken', csrfToken);
            formData.append('newFiles[]', photos[i]);

            calls.push({
                url : destination,
                data: formData,
                type: 'POST',
                contentType: false,
                processData: false,
            });
        }

        let current = 0;
        function doCall()
        {
            //check to make sure there are more requests to make
            if (current < calls.length)
            {
                $('#upload-progress').html('Bezig met verwerken foto ' + (current + 1) + ' van ' + calls.length);
                //make the AJAX request with the given info from the array of objects
                $.ajax(calls[current]
                ).done(function() {
                    numSucceeded++;
                }).fail(function() {
                    numFailed++;
                }).always(function()
                {
                    current++;
                    doCall();
                });
            }
            else
            {
                document.getElementById('newFile').value = null;
                $('#add-photo-submit').val('Uploaden');
                $('#upload-progress').html('');
                alert(numSucceeded + ' bestanden succesvol geupload, ' + numFailed + ' mislukt.');
            }
        }

        doCall();
    });
});
