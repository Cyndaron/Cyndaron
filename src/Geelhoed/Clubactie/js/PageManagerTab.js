window.addEventListener("DOMContentLoaded", () =>
{
    $('.pm-confirm-tickets').on('click', function() {
        let subscriberId = $(this).data('id');

        $('#pm-confirm-tickets-save').off();
        $('#pm-confirm-tickets-save').on('click', function() {
            $.post('/api/clubactie/confirm-tickets/' + subscriberId, $('#pm-confirm-tickets-form').serialize())
                .done(function() {
                    alert('Loten bevestigd!');
                    const numTickets = document.getElementById('num-tickets').value;
                    document.getElementById('num-sold-tickets-' + subscriberId).innerText = numTickets;
                    document.getElementById('verified-status-' + subscriberId).innerText = '✔';
                    document.getElementById('mail-sent-' + subscriberId).innerText = '✔';
                    $('#pm-confirm-tickets').modal('hide');
                });
        });
    });

    document.getElementById('pm-mail-everyone').addEventListener('click', function(event) {
        const payload = new FormData();
        payload.append('csrfToken', event.target.getAttribute('data-csrf-token'));
        fetch('/api/webwinkel/mail-everyone', { method: 'POST', body: payload })
            .then((response) => {
                if (response.ok) {
                    location.reload();
                }
            });
    });
});
