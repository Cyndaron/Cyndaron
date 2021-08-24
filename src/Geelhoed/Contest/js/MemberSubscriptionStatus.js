'use strict';

$(document).ready(function()
{
    $('.gcv-cancel-subscription').on('click', function ()
    {
        const memberId = $(this).data('member-id');
        const contestMemberId = $(this).data('contest-member-id');
        const csrfToken = $(this).data('csrf-token');

        $.post({
            url: '/api/contest/cancelSubscription/' + contestMemberId,
            data: { csrfToken: csrfToken }
        }).done(function (data) {
            alert(data.message);
            $('#gcv-member-subscriptions-' + memberId).remove();
        }).fail(function (data) {
            alert(data.message);
        });
    });
});
