<?php
require_once('functies.gebruikers.php');

session_start();
session_destroy();

session_start();
nieuweMelding('U bent afgemeld.');
header('Location: ./');
