Versie 5.0 (Scupi) (in ontwikkeling)
- Code
  - Alles is overgezet naar OOP met namespacing
  - PSR-4-autoloader
  - Tags zonder spaties ervoor/erna gefixt (nodig voor PHP 7)
  - Enkele notices verholpen
  - Instellingen in het configuratiepaneel worden nu geëscapet
  - Inline Javascript en CSS is deels verplaatst naar eigen bestanden
  - Een deel van de uitgecommente code is verwijderd
  - Code houdt zich beter aan codestijl
- Nieuwe features
  - Ingebedde afbeeldingen worden uit de tekst geëxtraheerd en in de map `afb/via-editor` geplaatst
  - CKeditor geüpdatet
  - CKeditor neemt achtergrondkleur van de pagina over
- Verwijderde onderdelen
  - Lingo-klasse
  - Klassiek pictogrammenthema
- Look and feel
  - Native font stack
  - Gebruik van Bootstrap en Lightbox
  - Uiterlijk is gemoderniseerd
  - Verbeteringen voor mobiele apparatuur: viewport
  - Losse spaties voor en na samenvattingen op categoriepagina's verwijderd
- Documentatie
  - Changelog toegevoegd
  - Licentie-informatie van code van derde partijen toegevoegd
  - Enkele fixes aan PHPDoc

Versie 4.0 (Roma)
- 'Hoofdstukken' en 'artikelen' vervangen door statische pagina's (subs)

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

Versie 1.1 (Cantium)
- Snelle fix voor de mojibake
- Preview bij het bewerken van pagina's

Versie 1.0 (Londinium)
- Eerste versie