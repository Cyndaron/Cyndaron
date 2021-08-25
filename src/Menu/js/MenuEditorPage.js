'use strict';

const mmFields = ['id', 'link', 'alias', 'isDropdown', 'isImage', 'priority'];

$(document).ready(function ()
{
    $('.mm-delete-item').on('click', function()
    {
        let index = $(this).data('id');
        let csrfToken = $('#mm-menutable').data('delete-csrf-token');

        del('Weet u zeker dat u dit menu-item wilt verwijderen?', function () {
            $.post('/api/menu/deleteItem/' + index, { csrfToken: csrfToken }).done(function () {
                location.reload();
            });
        });
    });

    $('#mm-create-item').on('click', function () {
        let csrfToken = $(this).data('csrf-token');

        $('#mm-csrf-token').val(csrfToken);
        $('#mm-id').val('');

        mmFields.forEach(function(item) {
            if (item === 'isDropdown' || item === 'isImage')
                $('#mm-' + item).prop('checked', false);
            else
                $('#mm-' + item).val('');
        });
    });

    $('.mm-edit-item').on('click', function () {

        $('#mm-csrf-token').val($('#mm-menutable').data('edit-csrf-token'));
        $('#mm-id').val($(this).data('id'));
        let currentItem = $(this);

        mmFields.forEach(function(item) {
            if (item === 'isDropdown' || item === 'isImage')
                $('#mm-' + item).prop('checked', currentItem.data(item.toLowerCase()) === 1);
            else
                $('#mm-' + item).val(currentItem.data(item));
        });
    });

    $('#mm-edit-item-save').on('click', function () {
        let id = $('#mm-id').val();
        let action = (id === '') ? 'addItem' : 'editItem';

        let payload = { csrfToken: $('#mm-csrf-token').val() };
        mmFields.forEach(function (item) {
            if (item === 'isDropdown' || item === 'isImage')
                payload[item] = $('#mm-' + item).prop('checked') ? '1' : '0';
            else
                payload[item] = $('#mm-' + item).val();
        });

        $.post('/api/menu/' + action + '/' + id, payload).done(function() {
            location.reload();
        });
    });
});