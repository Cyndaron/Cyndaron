'use strict';

$(document).ready(function()
{
    $('#search-by-age-submit').on('click', function ()
    {
        const age = $('#search-by-age-age').val();
        if (age > 0)
        {
            window.location = '/location/searchByAge/' + age;
        }
    });
});
