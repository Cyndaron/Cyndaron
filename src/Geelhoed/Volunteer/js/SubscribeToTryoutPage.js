'use strict';

$(document).ready(function() {
    const form = document.getElementById('subscribe-form');
    const eventId = document.getElementById('eventId').value;

    document.getElementById('submit').addEventListener('click', function(event) {
        fetch('/api/vrijwilligers/inschrijven-voor-tryout/' + eventId, {
        method: 'POST',
        body: new FormData(form)
    })
        .then((response) => {
            response.json().then((json) => {
                if (response.ok)
                {
                    alert('Bedankt voor je inschrijving! We zien je graag terug op het toernooi!');
                    window.location.href="/";
                }
                else
                {
                    if (json.status === 'partially_full')
                    {
                        alert(json.message);
                    }
                    else if (json.status === 'full')
                    {
                        alert(json.message);
                    }
                    else
                    {
                        alert(json.message);
                    }
                }
            });
        });
    });
});
