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

    $('.pm-changeorder').on('click', function() {
        let categoryId = $(this).data('id');
        $('#pm-change-order-category-id').val(categoryId);

        let container = $('#pm-change-order-form-container');
        container.html('');

        $.get('/api/category/underlyingPages/' + categoryId, {}, null, 'json')
            .done(function (data) {
                for (let i = 0; i < data.length; i++)
                {
                    let currentPage = data[i];

                    let fieldId = 'pm-change-order-' + currentPage.type + '-' + currentPage.id;
                    let row = $('<div class="form-group row">').appendTo(container);
                    $('<label for="' + fieldId + '" class="col-md-3 col-form-label">' + currentPage.name + ':</label>').appendTo(row);
                    $('<div class="col-md-6"><input type="number" class="form-control" id="' + fieldId + '" name="' + currentPage.type + '-' + currentPage.id + '" value="' + currentPage.priority + '" required></div>').appendTo(row);
                }
            });

        $('#pm-change-order-save').off();
        $('#pm-change-order-save').on('click', function() {
            $.post('/api/category/changeOrder/' + categoryId, $('#pm-change-order-form').serialize())
                .done(function() {
                    $('#pm-change-order').modal('hide');
                });
        });
    });

});