'use strict';

$(document).ready(function () {
    $('.pm-edit').on('click', function() {
        const csrfTokenField = $('#pm-edit-modal-form input[name=csrfToken]');
        const nameField = $('#pm-edit-modal-name');
        const juniorFeeField = $('#pm-edit-modal-junior-fee');
        const seniorFeeField = $('#pm-edit-modal-senior-fee');

        const sportId = $(this).data('id');
        nameField.val($(this).data('name'));
        juniorFeeField.val($(this).data('junior-fee'));
        seniorFeeField.val($(this).data('senior-fee'));

        $('#pm-edit-modal-save').off();
        $('#pm-edit-modal-save').on('click', function() {
            const payload = {
                csrfToken: csrfTokenField.val(),
                name: nameField.val(),
                juniorFee: juniorFeeField.val().replaceAll(',', '.'),
                seniorFee: seniorFeeField.val().replaceAll(',', '.'),
            };

            $.post('/api/sport/edit/' + sportId, payload)
                .done(function() {
                    new bootstrap.Modal('#pm-edit-modal').hide();
                    location.reload();
                });
        });
    });

});
