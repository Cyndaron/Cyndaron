'use strict';

$(document).ready(function()
{
    $('#search-by-age-submit').on('click', function ()
    {
        const age = $('#search-by-age-age').val();
        const sport = $("input[name='search-by-age-sport']:checked").val();
        if (age > 0 && sport > 0)
        {
            window.location = '/locaties/op-leeftijd/' + age + '/' + sport;
        }
    });
});
