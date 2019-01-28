'use strict';

$(document).ready(function () {
    $('.pm-delete').on('click', function () {

        let id = $(this).data('id');
        let type = $(this).data('type');

        $('#confirm-dangerous .modal-body').html('Weet u zeker dat u dit wilt verwijderen?');
        $('#confirm-dangerous-yes').off();
        $('#confirm-dangerous-yes').on('click', function()
        {
            $.ajax('/pagemanager/' + type + '/delete/' + id, gDefaultAjaxSettings).done(function() {
                $('#pm-row-' + type + '-' + id).remove();
                $('#confirm-dangerous').modal('hide');
            });
        });
        $('#confirm-dangerous').modal();
    });
    $('.pm-addtomenu').on('click', function () {
        let id = $(this).data('id');
        let type = $(this).data('type');

        $.ajax('/pagemanager/' + type + '/addtomenu/' + id, gDefaultAjaxSettings).done(function() {
            location.reload();
        });
    });

    $('#pm-create-category').on('click', function () {
        let settings = gDefaultAjaxSettings;
        settings.data = { name: $('#pm-category-new-name').val() };
        $.ajax('/pagemanager/category/new', settings).done(function() {
            location.reload();
        });
    });

    $('#pm-create-photoalbum').on('click', function () {
        let settings = gDefaultAjaxSettings;
        settings.data = { name: $('#pm-photoalbum-new-name').val() };
        $.ajax('/pagemanager/photoalbum/new', settings).done(function() {
            location.reload();
        });
    });

    $('#pm-create-friendlyurl').on('click', function () {
        let settings = gDefaultAjaxSettings;
        settings.data = {
            name: $('#pm-friendlyurl-new-name').val(),
            target:   $('#pm-friendlyurl-new-target').val()
        };
        $.ajax('/pagemanager/friendlyurl/new', settings).done(function() {
            location.reload();
        });
    });
});