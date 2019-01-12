'use strict';

$(document).ready(function ()
{
    $('.isDropdown').on('change', function()
    {
        let index = $(this).data('id');
        let isDropdown = $(this).is(':checked');
        console.log(isDropdown);
        $.post('menu-editor', { index: index , action: 'setDropdown', isDropdown: isDropdown });
    });
    $('.isImage').on('change', function()
    {
        let index = $(this).data('id');
        let isImage = $(this).is(':checked');
        $.post('menu-editor', { index: index , action: 'setImage', isImage: isImage });
    });
    $('.removeItem').on('click', function()
    {
        let index = $(this).data('id');
        $.post('menu-editor', { index: index , action: 'removeItem' });
    });
});