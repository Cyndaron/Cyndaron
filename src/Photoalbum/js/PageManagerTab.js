'use strict';

$(document).ready(function () {
    $('#pm-create-photoalbum').on('click', function () {
        let csrfToken = $(this).data('csrf-token');
        let data = {
            name: $('#pm-photoalbum-new-name').val(),
            csrfToken: csrfToken
        };
        $.post('/api/photoalbum/add', data).done(function() {
            location.reload();
        });
    });
});