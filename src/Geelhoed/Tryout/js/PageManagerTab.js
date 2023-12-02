'use strict';

window.addEventListener("DOMContentLoaded", () => {
    const elements = document.getElementsByClassName('create-photoalbums');
    for (let i = 0; i < elements.length; i++)
    {
        elements[i].addEventListener('click', (event) => {
            const tryoutId = elements[i].attributes['data-id'].value;

            const csrfTokenDelete = elements[i].attributes['data-csrf-token-create-photoalbums'].value;
            const formData = new FormData();
            formData.append('csrfToken', csrfTokenDelete);

            const url = '/api/tryout/create-photoalbums/' + tryoutId;
            const response = fetch(url, {
                method: 'POST',
                body: formData,
            }).then((response) => {
                if (response.ok)
                {
                    alert('Fotoalbums aangemaakt!');
                    location.reload();
                }
                else
                {
                    alert('Er ging iets mis!');
                }
            });
        });
    }
});
