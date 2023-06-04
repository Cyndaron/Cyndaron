'use strict';

window.addEventListener("DOMContentLoaded", () => {
    document.getElementById('code-submit').addEventListener('click', () => {
        let code = document.getElementById('code').value;
        document.location = '/tryout/scores/' + code;
    });
});
