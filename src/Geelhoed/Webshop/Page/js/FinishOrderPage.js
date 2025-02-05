'use strict';

window.addEventListener("DOMContentLoaded", () =>
{
    const locationSelector = document.getElementById('locationId');
    locationSelector.selectedIndex = null;

    const hourSelector = document.getElementById('hourId');
    locationSelector.addEventListener('change', function(event)
    {
        const locationId = locationSelector.value;
        hourSelector.options.length = 0;

        if (locationId === '')
            return;

        fetch('/api/hour/byLocationFormatted/' + locationId)
            .then((response) => {
                if (response.ok)
                {
                    response.json().then((body) => {
                        for (const index of Object.getOwnPropertyNames(body.hours))
                        {
                            const desc = body.hours[index];
                            hourSelector.appendChild(new Option(desc, index));
                        }
                    });
                }
                else
                {
                    response.json().then((body) => {
                        alert(body.error);
                    });
                }
            });
    });
});
