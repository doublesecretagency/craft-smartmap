<?php

return [

    // Plugin title
    'Smart Map' => 'Smart Map',

    // Welcome page
    'Welcome to Smart Map!' => 'Benvenuto in Smart Map!',
    'Thanks for using Smart Map!' => 'Grazie per aver utilizzato Smart Map!',
    'Feel free to <a href="{emailLink}" target="_blank">contact us</a> with any feature requests, bug reports, or suggestions.' => 'Sentiti libero di <a href="{emailLink}" target="_blank">contattarci</a> per qualsiasi richiesta di funzionalità, segnalazioni di bug o suggerimenti.',
    'Create an Address field' => 'Crea un campo indirizzo',
    'View full documentation' => 'Vedi la documentazione',

    // Fieldtype configuration
    'Address' => 'Indirizzo',
    'Address Field Layout' => 'Layout del caampo indirizzo',
    'Manage how the subfields will be arranged.' => 'Gestisci come verranno organizzati i sottocampi.',
    'Label' => 'Etichetta',

    // Address field
    'Street Address' => 'Via',
    'Apartment or Suite' => 'Apartment or Suite',
    'City' => 'Città',
     // 'administrative_area_level_2' => 'Provincia',
    'State' => 'Regione',  // 'administrative_area_level_1'
    'Zip Code' => 'CAP',
    'Country' => 'Paese',
    'Latitude' => 'Latitudine',
    'Longitude' => 'Longitudine',
    'Search Addresses' => 'Ricerca Indirizzo',
    'Drag Pin' => 'Trascina il puntatore',

    // Search results modal
    'Showing 1 search result...' => 'Visualizzazione di un risultato inerente alla ricerca...',
    'Showing {total} search results...' => 'Visualizzazione di {total} risultati inerenti alla ricerca...',

    // Error messages
    'No address provided. Please enter a partial address to search for.' => 'Nessun indirizzo fornito. Si prega di inserire un indirizzo parziale da cercare.',
    'The geocode was successful but returned no results.' => 'Il geocode ha funzionato ma non ha restituito risultati.',
     // 'You are over your quota. If this is a shared server, enable <a href="{url}">Google Maps API Keys.</a>' => '<a href="{url}">X</a>',
     'Your request was denied for some reason.' => 'La tua richiesta è stata respinta per qualche motivo.',
    'Invalid request. Please provide more address information.' => 'Richiesta non valida. Si prega di fornire più informazioni sull\'indirizzo.',
    'Response from Google Maps API:' => 'Risposta dall\'API di Google Maps:',
    'Failed to execute cURL command.' => 'Impossibile eseguire il comando cURL.',
    'Unknown cURL response:' => 'Risposta cURL sconosciuta:',

    // Address field settings
    // 'Set defaults for "Drag Pin" modal map' => 'X',
    // 'When specifying a location via the "Drag Pin" modal, you may set default coordinates and zoom level.' => 'X',
    // 'Set a default pin location for modal?' => 'X',
    // 'Zoom' => 'X',

    // Settings page
    'Documentation & Feedback' => 'Documentazione e feedback',
    // 'All documentation can be found at <a href="{url}" target="_blank">doublesecretagency.com</a>.' => 'X <a href="{url}" target="_blank">X</a>',
    'You can reach us at {supportEmail}. All questions, comments, and suggestions are welcome!' => 'Puoi contattarci all\'indirizzo {supportEmail}. Tutte le domande, i commenti e i suggerimenti sono benvenuti!',

     // 'Google Maps API Keys' => 'X',
    'As of June 11, 2018, all Google Maps API requests must include an API key. <strong>Keyless access is no longer supported.</strong>' => 'A partire dal 11 giugno 2018, tutti gli usi dell\'API di Maps ora richiedono un progetto con un account di fatturazione collegato ad esso.',
    'To get your Google API keys, <a href="{url}" target="_blank">follow these instructions...</a>' => 'Per ottenere le API keys di Google, <a href="{url}" target="_blank"> segui queste istruzioni</a>... ',
     // 'Google API Server Key' => 'X',
    'Used for address lookups' => 'Utilizzata per ricerche sugli indirizzi',
     // 'Google API Browser Key' => 'X',
    'Used for static & dynamic map rendering' => 'Utilizzata per il rendering statico e dinamico delle mappe',

    'Geolocation' => 'Geolocalizzazione',
    'Depending on the purpose of your website, you may need to use <strong>IP detection</strong> and <strong>geolocation</strong> to automatically detect where your site visitors are located.' => 'A seconda dello scopo del tuo sito web, potresti dover utilizzare il <strong>Rilevamento IP</strong> e la <strong>Geolocalizzazione</strong> per rilevare automaticamente dove si trovano i visitatori del tuo sito.',
    'Select a service...' => 'Seleziona un servizio ...',
    'Geolocation is disabled.' => 'La geolocalizzazione è disabilitata.',
    'A free service, ipstack can perform IP address lookups automatically. Formerly known as FreeGeoIp.net.' => 'Servizio gratuito, ipstack può eseguire automaticamente la ricerca degli indirizzi IP.',
    'A paid subscription service, MaxMind provides more accurate results, with a greater guarantee of uptime.' => 'Servizio in abbonamento a pagamento, MaxMind fornisce risultati più accurati, con una maggiore garanzia di uptime.',

    'ipstack Configuration' => 'Configurazione ipstack',
    // 'Copy your <a href="{url}" target="_blank">API Access Key</a> and paste it here...' => 'X <a href="{url}" target="_blank">X</a>',

    'MaxMind Configuration' => 'Configurazione MaxMind',
    '<strong>Step 1:</strong> Subscribe to a <a href="{url}" target="_blank">Web Service...</a>' => '<strong> Passaggio 1: </strong> Iscriviti a un <a href="{url}" target="_blank"> servizio web ... </a>',
    '<strong>Step 2:</strong> Which <a href="{url}" target="_blank">Web Service</a> have you subscribed to?' => '<strong> Passaggio 2: </strong> a quale <a href="{url}" target="_blank"> servizio web </a> ti sei abbonato?',
    '<strong>Step 3:</strong> Copy your <a href="{url}" target="_blank">security access information</a> and paste it here...' => '<strong> Passaggio 3: </strong> copia le <a href="{url}" target="_blank"> informazioni di sicurezza </a> e incollale qui ...',
    '(service disabled)' => '(servizio disabilitato)',
     // 'User ID' => 'X',
     // 'License Key' => 'X',
    'If you have recently switched services, clear old geolocation results by visiting:' => 'Se hai recentemente attivato i servizi, cancella i vecchi risultati di geolocalizzazione visitando:',

    'Plugin Debug Page' => 'Pagina di debug del plugin',
    'Sometimes you need to know a little more about what the plugin is doing. This special page can give you insight into the geolocation capabilities of your site.' => 'A volte devi sapere qualcosa in più su cosa sta facendo il plugin. Questa pagina speciale può fornirti informazioni sulle funzionalità di geolocalizzazione del tuo sito.',

    // 'Loading map...' => 'X'

];
