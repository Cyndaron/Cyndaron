'use strict';

const fields = ['username', 'email', 'password', 'level', 'firstName', 'tussenvoegsel', 'lastName', 'role', 'comments', 'avatar', 'hideFromMemberList'];

$(document).ready(function () {

    $('#um-create-user').on('click', function () {
        let csrfToken = $(this).data('csrf-token');

        $('#um-csrf-token').val(csrfToken);
        $('#um-id').val('');

        fields.forEach(function(item) {
            if (item === 'level')
                $('#um-' + item).val(1);
            else if (item === 'hideFromMemberList')
                $('#um-' + item).prop('checked', false);
            else
                $('#um-' + item).val('');
        });

        $('#um-password-group').css('display', 'flex');
    });

    $('.um-edit-user').on('click', function () {

        $('#um-csrf-token').val($('#um-usertable').data('edit-csrf-token'));
        $('#um-id').val($(this).data('id'));
        let userItem = $(this);

        fields.forEach(function(item) {
            if (item === 'password')
                $('#um-' + item).val('');
            else if (item === 'hideFromMemberList')
                $('#um-' + item).prop('checked', userItem.data(item.toLowerCase()) === 1);
            else
                $('#um-' + item).val(userItem.data(item.toLowerCase()));
        });

        $('#um-password-group').css('display', 'none');
    });

    $('.um-resetpassword').on('click', function () {
        let id = $(this).data('id');
        let csrfToken = $('#um-usertable').data('resetpassword-csrf-token');
        $.post('/api/user/resetpassword/' + id, { csrfToken: csrfToken }).done(function () {
            alert('Wachtwoord gereset.');
        });
    });

    $('.um-updateAvatar').on('click', function () {
        let id = $(this).data('id');
        $('#um-update-avatar').prop('action', '/user/changeAvatar/' + id);
    });

    $('.um-delete').on('click', function ()
    {
        let id = $(this).data('id');
        let csrfToken = $('#um-usertable').data('delete-csrf-token');

        del('Weet u zeker dat u deze gebruiker wilt verwijderen?', function() {
            $.post('/api/user/delete/' + id, { csrfToken: csrfToken }).done(function () {
                location.reload();
            });
        });
    });

    $('#um-edit-user-save').on('click', function () {
        let id = $('#um-id').val();
        let action = (id === '') ? 'add' : 'edit';

        let payload = { csrfToken: $('#um-csrf-token').val() };
        fields.forEach(function (item) {
            if (item === 'hideFromMemberList')
                payload[item] = $('#um-' + item).prop('checked') ? '1' : '0';
            else
                payload[item] = $('#um-' + item).val();
        });

        $.post('/api/user/' + action + '/' + id, payload).done(function() {
            location.reload();
        });
    });
});