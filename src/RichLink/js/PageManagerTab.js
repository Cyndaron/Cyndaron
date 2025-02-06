'use strict';

"use strict";

function showRichLinkEditDialog(id, name, url, previewImage, blurb, openInNewTab, categories)
{
    new bootstrap.Modal('#pm-edit-dialog').show();

    $('#pm-edit-id').val(id);
    $('#pm-edit-name').val(name);
    $('#pm-edit-url').val(url);
    $('#pm-edit-previewImage').val(previewImage);
    $('#pm-edit-blurb').val(blurb);
    $('#pm-edit-openInNewTab').prop('checked', openInNewTab);
    $('.category-select').prop('checked', false);
    if (categories !== null && Array.isArray(categories))
    {
        for (let i = 0; i < categories.length; i++)
        {
            let categoryId = categories[i];
            $('#category-' + categoryId).prop('checked', true);
        }
    }
}

$(document).ready(function()
{
    $('#pm-new').on('click', function ()
    {
        showRichLinkEditDialog(null, null, null, null, null, false, null);
    });

    $('.pm-edit').on('click', function()
    {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let url = $(this).data('url');
        let previewImage = $(this).data('preview-image');
        let blurb = $(this).data('blurb');
        let openInNewTab = $(this).data('open-in-new-tab');
        let categoriesString = $(this).data('categories').toString();
        let categories = categoriesString.split(',');

        showRichLinkEditDialog(id, name, url, previewImage, blurb, openInNewTab, categories);
    });

    $('.pm-delete').on('click', function ()
    {
        let id = $(this).data('id');
        let csrfToken = $('#pm-table').data('csrf-token-delete');
        let row = $(this).parent().parent().parent();

        del('Weet u zeker dat u deze speciale link wilt verwijderen?', function()
        {
            $.post('/api/richlink/delete', { id: id, csrfToken: csrfToken }).done(function()
            {
                row.remove();
                new bootstrap.Modal('#confirm-dangerous').hide();
            });
        });
    });

    $('#pm-edit-save').on('click', function ()
    {
        let payLoad = {
            csrfToken: $('#pm-table').data('csrf-token-edit'),
            id: $('#pm-edit-id').val(),
            name: $('#pm-edit-name').val(),
            url: $('#pm-edit-url').val(),
            previewImage: $('#pm-edit-previewImage').val(),
            blurb: $('#pm-edit-blurb').val(),
            openInNewTab: $('#pm-edit-openInNewTab').prop('checked') ? 1 : 0,
        };
        $('.category-select').each(function() {
            let id = $(this).attr('id');
            payLoad[id] = $(this).prop('checked') ? 1 : 0;
        });

        $.post('/api/richlink/edit', payLoad).done(function () {
            location.reload();
        });
    });
});
