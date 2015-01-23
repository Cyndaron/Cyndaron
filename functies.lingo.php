<?php

class Lingo
{
	public static function geefTekst($id, $alttekst)
	{
                try
                {
                    include('sys/talen/'.geefInstelling('taal'));
                    if ($strings[$id]!=false)
                    {
                        return $strings[$id];
                    }
                    else
                    {
                        return $alttekst;
                    }
                } 
                catch (Exception $ex) 
                {
			return $alttekst;
                }
	}
}