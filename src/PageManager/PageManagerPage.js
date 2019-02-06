'use strict';

$(document).ready(function () {
    $('.pm-delete').on('click', function () {

        let id = $(this).data('id');
        let type = $(this).data('type');

        $('#confirm-dangerous .modal-body').html('Weet u zeker dat u dit wilt verwijderen?');
        $('#confirm-dangerous-yes').off();
        $('#confirm-dangerous-yes').on('click', function()
        {
            $.ajax('/' + type + '/delete/' + id, gDefaultAjaxSettings).done(function() {
                $('#pm-row-' + type + '-' + id).remove();
                $('#confirm-dangerous').modal('hide');
            });
        });
        $('#confirm-dangerous').modal();
    });
    $('.pm-addtomenu').on('click', function () {
        let id = $(this).data('id');
        let type = $(this).data('type');

        $.ajax('/' + type + '/addtomenu/' + id, gDefaultAjaxSettings).done(function() {
            location.reload();
        });
    });

    $('#pm-create-category').on('click', function () {
        let settings = gDefaultAjaxSettings;
        let csrfToken = $(this).data('csrf-token');
        settings.data = {
            name: $('#pm-category-new-name').val(),
            csrfToken: csrfToken
        };
        $.ajax('/category/add', settings).done(function() {
            location.reload();
        });
    });

    $('#pm-create-photoalbum').on('click', function () {
        let settings = gDefaultAjaxSettings;
        let csrfToken = $(this).data('csrf-token');
        settings.data = {
            name: $('#pm-photoalbum-new-name').val(),
            csrfToken: csrfToken
        };
        $.ajax('/photoalbum/add', settings).done(function() {
            location.reload();
        });
    });

    $('#pm-create-friendlyurl').on('click', function () {
        let settings = gDefaultAjaxSettings;
        let csrfToken = $(this).data('csrf-token');
        settings.data = {
            name: $('#pm-friendlyurl-new-name').val(),
            target:   $('#pm-friendlyurl-new-target').val(),
            csrfToken: csrfToken
        };
        $.ajax('/friendlyurl/add', settings).done(function() {
            location.reload();
        });
    });
});