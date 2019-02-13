'use strict';

$(document).ready(function ()
{
    $('.isDropdown').on('change', function()
    {
        let index = $(this).data('id');
        let isDropdown = $(this).is(':checked');
        let csrfToken = $(this).data('csrf-token');
        $.post('/menu/setDropdown/' + index, { isDropdown: isDropdown, csrfToken: csrfToken });
    });
    $('.isImage').on('change', function()
    {
        let index = $(this).data('id');
        let isImage = $(this).is(':checked');
        let csrfToken = $(this).data('csrf-token');
        $.post('/menu/setImage/' + index, { isImage: isImage, csrfToken: csrfToken });
    });
    $('.removeItem').on('click', function()
    {
        let index = $(this).data('id');
        let csrfToken = $(this).data('csrf-token');
        $.post('/menu/removeItem/' + index, { csrfToken: csrfToken });
    });
});