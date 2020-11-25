/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the ISC License. See the LICENSE file for more details.
 */

"use strict";

function plakLink()
{
    // Open het dialoogvenster voor het invoeren van een link
    $('.cke_button__link').first().click();

    setTimeout(function ()
    {
        var link = $('#verwijzing').val();
        var focus = $(':focus');
        // Het veld voor de link heeft standaardfocus
        focus.val(link);

        // Zet de selector voor het linktype op 'anders'
        focus.parent().parent().parent().parent().parent().find('select').val('');
    }, 800);
}

$('#plaklink').on('click', function() {plakLink();});

CKEDITOR.replace('ckeditor-parent', {
    removePlugins: 'flash',
    customConfig: '/js/ckeditor-config.js'
});
