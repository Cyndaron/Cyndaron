'use strict';

$(document).ready(function () {

    $('#um-create-user').on('click', function () {
        let csrfToken = $(this).data('csrf-token');

        $('#um-csrf-token').val(csrfToken);
        $('#um-id').val('');

        $('#um-username').val('');
        $('#um-email').val('');
        $('#um-password').val('');
        $('#um-level').val(1);

        $('#um-password-group').css('display', 'flex');
    });

    $('.um-edit-user').on('click', function () {
        let csrfToken = $('#um-usertable').data('edit-csrf-token');
        let id = $(this).data('id');
        let username = $(this).data('username');
        let email = $(this).data('email');
        let level = $(this).data('level');

        $('#um-csrf-token').val(csrfToken);
        $('#um-id').val(id);

        $('#um-username').val(username);
        $('#um-email').val(email);
        $('#um-password').val('');
        $('#um-level').val(level);

        $('#um-password-group').css('display', 'none');
    });

    $('.um-resetpassword').on('click', function () {
        let id = $(this).data('id');
        let csrfToken = $('#um-usertable').data('resetpassword-csrf-token');
        $.post('/user/resetpassword/' + id, { csrfToken: csrfToken }).done(function () {
            alert('Wachtwoord gereset.');
        });
    });

    $('#um-edit-user-save').on('click', function () {
        let id = $('#um-id').val();
        let csrfToken = $('#um-csrf-token').val();

        let username = $('#um-username').val();
        let email = $('#um-email').val();
        let password = $('#um-password').val();
        let level = $('#um-level').val();

        let action = (id === '') ? 'add' : 'edit';

        $.post('/user/' + action + '/' + id, {
            username: username,
            email: email,
            password: password,
            level: level,
            csrfToken: csrfToken
        }).done(function() {
            location.reload();
        });
    });
});