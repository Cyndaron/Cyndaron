'use strict';

$(document).ready(function () {
    $('.pm-delete').on('click', function () {

        let id = $(this).data('id');
        let type = $(this).data('type');
        let csrfToken = $(this).data('csrf-token');

        $('#confirm-dangerous .modal-body').html('Weet u zeker dat u dit wilt verwijderen?');
        $('#confirm-dangerous-yes').off();
        $('#confirm-dangerous-yes').on('click', function()
        {
            $.post('/' + type + '/delete/' + id, { csrfToken: csrfToken }).done(function() {
                $('#pm-row-' + type + '-' + id).remove();
                $('#confirm-dangerous').modal('hide');
            });
        });
        $('#confirm-dangerous').modal();
    });
    $('.pm-addtomenu').on('click', function () {
        let id = $(this).data('id');
        let type = $(this).data('type');
        let csrfToken = $(this).data('csrf-token');

        $.post('/' + type + '/addtomenu/' + id, { csrfToken: csrfToken }).done(function() {
            location.reload();
        });
    });

    $('#pm-create-category').on('click', function () {
        let csrfToken = $(this).data('csrf-token');
        let data = {
            name: $('#pm-category-new-name').val(),
            csrfToken: csrfToken
        };
        $.post('/category/add', data).done(function() {
            location.reload();
        });
    });

    $('#pm-create-photoalbum').on('click', function () {
        let csrfToken = $(this).data('csrf-token');
        let data = {
            name: $('#pm-photoalbum-new-name').val(),
            csrfToken: csrfToken
        };
        $.post('/photoalbum/add', data).done(function() {
            location.reload();
        });
    });

    $('#pm-create-friendlyurl').on('click', function () {
        let csrfToken = $(this).data('csrf-token');
        let data = {
            name: $('#pm-friendlyurl-new-name').val(),
            target:   $('#pm-friendlyurl-new-target').val(),
            csrfToken: csrfToken
        };
        $.post('/friendlyurl/add', data).done(function() {
            location.reload();
        });
    });
});