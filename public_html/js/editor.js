/**
 * Copyright © 2009-2023 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */

"use strict";

$('.internal-link-insert').on('click', function() {
    const target = $(this).data('target');
    const linkButton = $(`#cke_${target} .cke_button__link`).first();
    if (linkButton.length === 0)
    {
        return;
    }

    // Open the Insert Link dialog
    linkButton.click();

    setTimeout(function ()
    {
        const href = $(`.internal-link-href[data-target=${target}]`).val();
        // After opening the dialog, the focus will be on the link field, so we can simply "paste" our value
        const linkInput = $(':focus');
        linkInput.val(href);

        // Set the selector for the protocol to "other", to allow for relative URLs
        const protocolSelector = focus.parent().parent().parent().parent().parent().find('select');
        protocolSelector.val('');
    }, 800);
});

CKEDITOR.replaceAll('ckeditor-parent', {
    removePlugins: 'flash',
    customConfig: '/js/ckeditor-config.js'
});
