'use strict';

$(document).ready(function () {
    $('#pm-create-category').on('click', function () {
        let name = $('#pm-category-new-name').val();
        if (!name) {
            return;
        }
        let csrfToken = $(this).data('csrf-token');
        let data = {
            name: name,
            csrfToken: csrfToken
        };
        $.post('/api/category/add', data).done(function() {
            location.reload();
        });
    });
});