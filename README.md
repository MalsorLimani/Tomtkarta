# Tomtkarta
E-tjänst för att skapa en tomtkarta i PDF. Kräver ArcGis samt Geosecma for ArcGis.

Utvecklad av Malsor Limani, @Malsor, malsor@limani.se

Licens: CC-BY-NC-SA - https://creativecommons.org/licenses/by-nc-sa/4.0/

Se e-tjänsten i användning hos Jönköpings kommun, https://karta.jonkoping.se/app/tomtkarta/

*************************************************************************************
**Följ instruktionerna nedan, ingen ytterliggare support ges.**
*************************************************************************************

OBS!!! 
För att applikationen ska fungera krävs Geosecma for ArcGis samt modulen Geosecma Fastighet. 
Applikationen är utvecklad mot Geosecma/ArcGis-version 10.4.1.



1. Skapa och publicera en ArcGis-tjänst som innehåller de lager ni vill ska synas i Tomtkartan.

2. Skriv ner tjänstens url (slutar på "/MapServer") och kopiera över de 2 MXD-filerna (utskriftsmallar) i mappen till "X:/Program Files/ArcGIS/Server/Templates/ExportWebMapTemplates" på ArcGis-servern där tjänsten publicerades.

3. Efter publiceringen, säkerställ att tjänsten är åtkomlig externt, annars kan inte denna app användas externt.

4. Öppna "get.php" med anteckningar eller Notepad++ (https://notepad-plus-plus.org/download/v7.5.5.html).

5. Fyll i parametrar enligt instruktion i huvudet och spara.

6. Öppna "sok_adr.php" och fyll i parametrar enligt instruktion och spara.

7. Publicera hela mappen där denna fil ligger på en webbserver (Ta gärna hjälp av IT).

8. Säkerställ med IT att PHP-extension är påslaget på webbservern och att webbservern når er ArcGis-server ovan på port 80 eller 443.

9. Starta om ArcGis-server och surfa till er tomtkarte-app som nu bör fungera.

10. Maila gärna till Malsor och tacka om ni gillar appen :)

OBS!!!
Om uppdatering av Geosecma/ArcGis sker, måste utskriftsmallar för Tomtkartan kopieras på nytt till ArcGis-servern enligt steg 2.
