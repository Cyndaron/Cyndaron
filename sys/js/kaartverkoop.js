
function increase(vak)
{
    var element=document.getElementById(vak);
    if(element.value<100)
    {
        element.value++;
        berekenTotaalprijs()
    }
}
function decrease(vak)
{
    var element=document.getElementById(vak);
    if(element.value>0)
    {
        element.value--;
        berekenTotaalprijs()
    }
}

function checkFormulier()
{
    if(document.getElementById('antispam').value.toUpperCase() !== 'VLISSINGEN')
        return false;

    if(document.getElementById('prijsvak').innerHTML=="€&nbsp;0,00")
        return false;

    if(document.getElementById('prijsvak').innerHTML=="€&nbsp;-")
        return false;

    var achternaam=document.getElementById('achternaam').value;
    var voorletters=document.getElementById('voorletters').value;
    var emailadres=document.getElementById('e-mailadres').value;
    var ophalenDoorKoorlid = document.getElementById('ophalen_door_koorlid').checked;

    if(!(achternaam.length>0 && voorletters.length>0 && emailadres.length>0))
        return false;

    if (document.getElementById('bezorgen').checked || (bezorgenVerplicht && !ophalenDoorKoorlid))
    {
        var straatnaam_en_huisnummer=document.getElementById('straatnaam_en_huisnummer').value;
        var postcode=document.getElementById('postcode').value;
        var woonplaats=document.getElementById('woonplaats').value;

        if(!(straatnaam_en_huisnummer.length>0 && postcode.length>0 && woonplaats.length>0))
            return false;
    }

    if (ophalenDoorKoorlid && document.getElementById('naam_koorlid').value.length < 2)
        return false;

    if (buitenland && document.getElementById('naam_koorlid').value.length < 2)
        return false;

    return true;
}

function blokkeerFormulierBijOngeldigeInvoer()
{
    var invoerIsCorrect=checkFormulier();

    document.getElementById('verzendknop').disabled=!invoerIsCorrect;
}

function postcodeLigtInWalcheren(postcode)
{
    if (buitenland == true)
        return false;

    postcode = parseInt(postcode);

    if (postcode >= 4330 && postcode <= 4399)
        return true;
    else
        return false;
}

$('.aantalKaarten-increase').on('click', function() { increase('kaartsoort-' + $(this).attr('data-kaartsoort')); });
$('.aantalKaarten-decrease').on('click', function() { decrease('kaartsoort-' + $(this).attr('data-kaartsoort')); });
$('.berekenTotaalprijsOpnieuw').on('click', function() { berekenTotaalprijs(); });

setInterval(blokkeerFormulierBijOngeldigeInvoer, 1000);
setInterval(berekenTotaalprijs, 1000);

