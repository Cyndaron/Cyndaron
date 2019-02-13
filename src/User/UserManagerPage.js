'use strict';

$(document).ready(function () {
    $('.um-resetpassword').on('click', function () {
        let id = $(this).data('id');
        $.post('/user/resetpassword/' + id, {}).done(function () {
            alert('Wachtwoord gereset.');
        });
    });
});