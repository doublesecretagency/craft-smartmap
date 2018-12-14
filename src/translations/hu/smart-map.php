<?php

return [

    // Plugin title
    'Smart Map' => 'Smart Map',

    // Welcome page
    'Welcome to Smart Map!' => 'Üdv a Smart Map-ban!',
    'Thanks for using Smart Map!' => 'Köszönjük, hogy a Smart Map-ot használod!',
    'Feel free to <a href="{emailLink}" target="_blank">contact us</a> with any feature requests, bug reports, or suggestions.' => '<a href="{emailLink}" target="_blank">Keress minket</a> bizalommal, ha bármilyen észrevételed, javaslatod van, vagy hibajelentéseddel segíteni tudunk.',
    'Create an Address field' => 'Cím mező létrehozása',
    'View full documentation' => 'Teljes dokumentáció megtekintése',

    // Fieldtype configuration
    'Address' => 'Cím',
    'Address Field Layout' => 'Cím mező elrendezése',
    'Manage how the subfields will be arranged.' => 'Almezők elrendezésének beállítása',
    'Label' => 'Címke',

    // Address field
    'Street Address' => 'Utca',
    'Apartment or Suite' => 'Házszám',
    'City' => 'Település',
    'State' => 'Állam',
    'Zip Code' => 'Irányítószám',
    'Country' => 'Megye',
    'Latitude' => 'Szélesség',
    'Longitude' => 'Hosszúság',
    'Search Addresses' => 'Címek keresése',
    'Drag Pin' => 'Jelölő ledobása',

    // Search results modal
    'Showing 1 search result...' => '1 keresési találat megjelenítése...',
    'Showing {total} search results...' => '{total} keresési találat megjelenítése...',

    // Error messages
    'No address provided. Please enter a partial address to search for.' => 'Nincs cím megadva. Kérlek, adj meg részleges címet, amire keresni szeretnél.',
    'The geocode was successful but returned no results.' => 'A geokódolás sikeres volt, de nem kaptunk eredményt.',
    'You are over your quota. If this is a shared server, enable <a href="{url}">Google Maps API Keys.</a>' => 'Túllépted a kvótádat. Ha ez egy osztott szerver, engedélyezd a <a href="{url}">Google Maps API kulcsokat</a>. ',
    'Your request was denied for some reason.' => 'A kérésedet valamilyen okból megtagadták.',
    'Invalid request. Please provide more address information.' => 'Érvénytelen kérés. Kérlek, adj meg több részletet a címről.',
    'Response from Google Maps API:' => 'A Google Maps API válasza:',
    'Failed to execute cURL command.' => 'A cURL parancs végrehajtása sikertelen.',
    'Unknown cURL response:' => 'Ismeretlen cURL válasz:',

    // Address field settings
    'Set defaults for "Drag Pin" modal map' => 'Alapértelmezések beállítása a "Jelölő ledobása" felugró térképhez.',
    'When specifying a location via the "Drag Pin" modal, you may set default coordinates and zoom level.' => 'Beállíthatsz alapértelmezett koordinátákat és nagyítási szintet arra az esetre, amikor a helyzetet a "Jelölő ledobása" felugró ablakban határozod meg,',
    'Set a default pin location for modal?' => 'Jelölő alapértelmezett helyzetének meghatározása.',
    'Zoom' => 'Nagyítás',

    // Settings page
    'Documentation & Feedback' => 'Dokumentáció & visszajelzés',
    'All documentation can be found at <a href="{url}" target="_blank">doublesecretagency.com</a>.' => 'Minden dokumentáció elérhető a <a href="{url}" target="_blank">doublesecretagency.com</a> címen.',
    'You can reach us at {supportEmail}. All questions, comments, and suggestions are welcome!' => 'Elérsz minket a {supportEmail} e-mail címen. Minden kérdést, megjegyzést, javaslatot szívesen fogadunk.',

    'Google Maps API Keys' => 'Google Maps API kulcs',
    'As of June 11, 2018, all Google Maps API requests must include an API key. <strong>Keyless access is no longer supported.</strong>' => '2018. június 11-től kezdődöen a Google Maps API hívásoknak API kulcsot kell használniuk. <strong>A kulcs nélküli hozzáférés már nem támogatott. </strong>',
    'To get your Google API keys, <a href="{url}" target="_blank">follow these instructions...</a>' => 'A Google Maps API kulcsok beszerzéséhez <a href="{url}" target="_blank">kövesd ezeket az utasításokat</a>!',
    'Google API Server Key' => 'Google API szerver kulcs',
    'Used for address lookups' => 'A címek kereséséhez használjuk.',
    'Google API Browser Key' => 'Google API böngésző kulcs',
    'Used for static & dynamic map rendering' => 'A statikus és dinamikus térképek megjelenítéséhez használjuk.',

    'Geolocation' => 'Geolokáció',
    'Depending on the purpose of your website, you may need to use <strong>IP detection</strong> and <strong>geolocation</strong> to automatically detect where your site visitors are located.' => 'Az oldalad jellegétől függően szükséged lehet <strong>IP cím érzékelésre</strong> és <strong>geolokációra</strong> annak érdekében, hogy automatikusan meghatározhasd, hol tartózkodnak a látogatóid. ',
    'None' => 'Nincs',
    'Select a service...' => 'Válassz szolgáltatást...',
    'Geolocation is disabled.' => 'Geolokáció letiltva.',
    'A free service, ipstack can perform IP address lookups automatically. Formerly known as FreeGeoIp.net.' => 'Az ipstack ingyenes szolgáltatás, mely képes meghatározni az IP címek helyzetét. Korábban FreeGeoIp.net néven működött.',
    'A paid subscription service, MaxMind provides more accurate results, with a greater guarantee of uptime.' => 'Fizetős szolgáltatásként a MaxMind pontosabb eredményeket szolgáltat, magasabb rendelkezésre állással.',

    'ipstack Configuration' => 'ipstack beállítás',
    'Copy your <a href="{url}" target="_blank">API Access Key</a> and paste it here...' => 'Másold ki az <a href="{url}" target="_blank">API hozzáférési kulcsot</a> és illeszd be ide...',

    'MaxMind Configuration' => 'MaxMind beállítás',
    '<strong>Step 1:</strong> Subscribe to a <a href="{url}" target="_blank">Web Service...</a>' => '1. lépés: <a href="{url}">Regisztrálj</a> egy webes szolgáltatásba...',
    '<strong>Step 2:</strong> Which <a href="{url}" target="_blank">Web Service</a> have you subscribed to?' => '2. lépés: Melyik <a href="{url}">webes szolgáltatásba</a> regisztráltál?',
    '<strong>Step 3:</strong> Copy your <a href="{url}" target="_blank">security access information</a> and paste it here...' => '3. lépés: Másold ki a <a href="{url}">biztonságos hozzáférési információkat</a> és illeszd be őket ide...',
    '(service disabled)' => '{service disabled}',
    'User ID' => 'Felhasználói azonosító (User ID)',
    'License Key' => 'Licensz kulcs',
    'If you have recently switched services, clear old geolocation results by visiting:' => 'Ha mostanában váltottál szolgáltatást, ürítsd ki az elavult geolokációs eredményeket meglátogatva ezt a címet:',

    'Plugin Debug Page' => 'Beépülő modul hibakeresési oldala',
    'Sometimes you need to know a little more about what the plugin is doing. This special page can give you insight into the geolocation capabilities of your site.' => 'Olykor szükséged lehet arra, hogy tudd, mit is csinál a beépülő modul. Ezen az oldalon áttekintést kaphatsz arról, hogy milyen geolokációs képességekkel is rendelkezik az oldalad.',

    'Loading map...' => 'Térkép betöltése...',

];
