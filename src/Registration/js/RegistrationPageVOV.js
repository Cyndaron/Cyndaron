/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the ISC License. See the LICENSE file for more details.
 */

'use strict';

$('#currentChoir').on('change', function () {
    let val = $(this).val();
    let option = $(this).find('option[value="' + val + '"]').first();
    $('input[name=registrationGroup]').val(option.data('registration-group'));
});

$('input[name=participatedBefore]').on('change', function()
{
    let explanationWrapper = $('#participatedBeforeWrapper');
    if ($(this).val() === '1')
        explanationWrapper.css('display', 'block');
    else
        explanationWrapper.css('display', 'none');
});
