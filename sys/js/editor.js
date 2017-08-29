/*
 * Copyright © 2009-2017, Michael Steenbeek
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
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
