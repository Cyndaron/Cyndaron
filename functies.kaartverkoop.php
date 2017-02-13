<?php

const STOELEN_PER_RIJ = 300;

function postcodeLigtInWalcheren($postcode)
{
	$postcode = intval($postcode);

	if ($postcode >= 4330 && $postcode <= 4399)
		return TRUE;
	else
		return FALSE;
}

function naarEuro($bedrag)
{
	return '&euro;&nbsp;'.number_format($bedrag, 2, ',', '.');
}

function naarEuroPlain($bedrag)
{
	return '€ '.number_format($bedrag, 2, ',', '.');
}

function boolNaarTekst($bool)
{
	if ($bool == true)
		return 'Ja';
	return 'Nee';
}
