"use strict";

$('.mc-speler-avatar').on('mouseover', function() {
   $(this).src = $(this).attr('data-achteraanzicht');
});

$('.mc-speler-avatar').on('mouseout', function() {
    $(this).src = $(this).attr('data-vooraanzicht');
});