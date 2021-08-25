/**
 * Copyright © 2011-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */

"use strict";

$('.mc-speler-avatar').on('mouseover', function() {
   $(this).attr('src', $(this).attr('data-achteraanzicht'));
});

$('.mc-speler-avatar').on('mouseout', function() {
    $(this).attr('src', $(this).attr('data-vooraanzicht'));
});
