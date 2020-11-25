/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */

'use strict';

$('#testColors').on('click', function ()
{
    let body = document.getElementsByTagName('body');
    let bodyset = document.getElementsByName('backgroundColor');
    body[0].style.backgroundColor = bodyset[0].value;
    let menu = document.getElementsByClassName('menu');
    let menuset = document.getElementsByName('menuColor');
    let menubg = document.getElementsByName('menuBackground');
    menu[0].style.backgroundColor = menuset[0].value;
    menu[0].style.backgroundImage = "url('" + menubg[0].value + "')";
    let article = document.getElementsByClassName('inhoud');
    let articleset = document.getElementsByName('articleColor');
    article[0].style.backgroundColor = articleset[0].value;
});
