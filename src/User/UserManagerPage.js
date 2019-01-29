'use strict';

$(document).ready(function () {
    $('.um-resetpassword').on('click', function () {
        let id = $(this).data('id');
        $.ajax('/user/resetpassword/' + id, gDefaultAjaxSettings).done(function () {
            alert('Wachtwoord gereset.');
        });
    });
});