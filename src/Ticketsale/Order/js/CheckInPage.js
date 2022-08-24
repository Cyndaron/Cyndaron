'use strict';

let scanButton = document.getElementById('start-scan');
const inputField = document.getElementById('barcode');
scanButton.addEventListener('click', function ()
{
    inputField.focus();
});

