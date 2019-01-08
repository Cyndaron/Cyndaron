Versie 5.3 (Lychnidus)
- Afbeeldingen in lopende tekst zijn nu automatisch responsief (#57)
- Fix schuifbalken op inhoud (#58)
- Fotoalbum: bijschriften zijn nu beter leesbaar (#51)
- Gebruik altijd hetzelfde pictogram voor inloggen (#60)
- Gebruik Reply-To bij versturen e-mail (#53)
- Gebruik versienummer bij opvragen CSS (#50)
- Handel redirect naar HTTPS af in PHP
- Kaartverkoop: CSS-fixes (#48 en #49)
- Kaartverkoop: gebruik `type="email"` voor e-mailadres op bestelformulier (#44)
- Kaartverkoop: mogelijkheid tot verwijderen bestellingen (#52)
- Mogelijkheid tot gebruik van plaatjes als menu-item
- Mogelijkheid tot includen van extra head- en bodycode (#61)
- Mogelijkheid tot inloggen met e-mailadres
- Mogelijkheid tot inloggen als niet-beheerder (#28)
- Mogelijkheid tot nesten van categorieën (#22)
- Mogelijkheid tot resetten wachtwoord
- Mogelijkheid tot vereiste inlog
- Pagina-overzicht is nu gesplitst op inhoudssoort (#74)
- TLS-verbindingen zijn nu verplicht

Versie 5.2.1
- Fix "Content-Type"-header in betalingsbevestiging kaartverkoop

Versie 5.2 (Stobi)
- Beveilig cookies beter
- Stel referrer-policy in
- Fix weergave meldingen in klassiek menu (kan nog verbeterd worden, #40)
- Voeg een CSP toe
- Friendly-url-preview in de editor toont nu het juiste protocol (#42)
- Plaats inlogknop klassiek menu beter (kan nog verbeterd worden, #39)
- Verbeter overzicht gereserveerde plaatsen in kaartverkoopmodule
- Fix bevestigen van betalingen in kaartverkoopmodule (#43)

Versie 5.1 (Heraclea Lyncestis)
- Verwijder inline scripting uit Cyndaron zelf (CKeditor is nog niet aangepakt)
- Gebruik password_hash voor het opslaan van wachtwoorden in plaats van SHA512 (#14)
- Gebruik $_POST niet meer buiten Request
- Update CKeditor naar 4.7.2
- Inlogknop van klassiek menu kan nu gestyled worden (#39)
- Integratie met Twitter Cards (#41)

Versie 5.0.1
- Slecht werkende code voor het op ware grootte tonen van foto's in fotoalbum verwijderd
- Klassiek menu was niet goed afgesloten
- Ontbrekende bestandenkast toegevoegd

Versie 5.0 (Scupi)
- Code
  - Alles is overgezet naar OOP met namespacing (#23, #24)
  - PSR-4-autoloader
  - Tags zonder spaties ervoor/erna gefixt (nodig voor PHP 7)
  - Enkele notices verholpen (#12)
  - Instellingen in het configuratiepaneel worden nu geëscapet
  - Inline Javascript en CSS is deels verplaatst naar eigen bestanden
  - Een deel van de uitgecommente code is verwijderd
  - Code houdt zich beter aan codestijl
- Nieuwe features
  - Ingebedde afbeeldingen worden uit de tekst geëxtraheerd en in de map `afb/via-editor` geplaatst
  - CKeditor geüpdatet
  - CKeditor neemt achtergrondkleur van de pagina over
- Verwijderde onderdelen
  - Lingo-klasse (#3)
  - Klassiek pictogrammenthema
- Look and feel
  - Native font stack (#25)
  - Gebruik van Bootstrap (#7)
  - Uiterlijk is gemoderniseerd (#6, #8)
  - Verbeteringen voor mobiele apparatuur: viewport
  - Losse spaties voor en na samenvattingen op categoriepagina's verwijderd (#5)
  - Fotoalbum gebruikt nu Lightbox (#2)
- Documentatie
  - Changelog toegevoegd
  - Licentie-informatie van code van derde partijen toegevoegd (#16, #20, #32)
  - Enkele fixes aan PHPDoc

Versie 4.0 (Roma)
- 'Hoofdstukken' en 'artikelen' vervangen door statische pagina's (subs)
- Overzichtspagina voor statische pagina's, categorieën, fotoboeken en friendly URL's

Versie 3.0 (Athenae)
- Mysql-extensie vervangen door PDO
- Editor is nu WYSIWYG (CKeditor)
- Fotoboeken kunnen nu een beschrijving hebben
- Betere HTML: paginalayout werkt met `<div>` in plaats van `<table>`, menu is gecodeerd als een `<ul>`, artikelen staan nu tussen `<p>`-tags in plaats van `<br /><br />`
- Nieuw pictogrammenthema: monochroom

Versie 2.2 (Tolosa)
- Diverse XSS-lekken gedicht
- Automatische preview
- Horizontale lijnen zijn wat subtieler
- Menu begint nu bij hoofdstuk met eerste bestaande ID, in plaats van aan te nemen dat dat 1 is.
- Favicon kan op andere locaties staan, en een ontbrekend favicon wordt nu goed opgevangen
- Bij het veranderen van achtergrondkleuren is er nu een preview beschikbaar

Versie 2.1 (Massilia)
- Hoekjes worden nu afgerond

Versie 2.0 (Lutetia)
- Overgestapt op UTF-8
- Ondersteuning voor extra menuitems buiten de hoofdstukken
- Editorpagina ziet er nu uit als een gewone pagina, inclusief menu

Versie 1.1 (Cantium)
- Snelle fix voor de mojibake
- Preview bij het bewerken van pagina's

Versie 1.0 (Londinium)
- Eerste versie
