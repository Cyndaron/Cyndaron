'use strict';

$(document).ready(function ()
{
    $('.delete-order').on('click', function ()
    {
        var orderId = $(this).attr('data-order-id');
        del("Weet u zeker dat u deze bestelling wilt verwijderen?", "kaarten-update-bestelling?bestellings_id=" + orderId + "&actie=delete");
    });
});
