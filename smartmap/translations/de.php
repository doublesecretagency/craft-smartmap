<?php

return array(

	// Plugin title
	'Smart Map' => 'Smart Map',

	// Fieldtype configuration
	'Address' => 'Adresse',
	'Address Field Layout' => 'Adressen Feld-Layout', // TODO: Maybe use just "Field Layout" in EN?
	'Manage how the subfields will be arranged.' => 'Legen Sie fest, wie die Teilfelder angeordnet werden sollen.',
	'Label' => 'Bezeichnung',

	// Address field
	'Street Address' => 'Straße und Hausnr.', // TODO: corrected this from "Straße, Nr." to prevent users from entering a comma
	'Apartment or Suite' => 'Adresszusatz',
	'City' => 'Ort',
	'State' => 'Bundesland/Region',
	'Zip Code' => 'PLZ',
	'Country' => 'Land',
	'Latitude' => 'Breitengrad',
	'Longitude' => 'Längengrad',
	'Search Addresses' => 'Adresse suchen',
	'Drag Pin' => 'Pin verschieben',

	// Search results modal
	'Showing 1 search result...' => '1 Suchergebnis...',
	'Showing {total} search results...' => '{total} Suchergebnisse...',

	// Error messages
	'No address provided. Please enter a partial address to search for.' => 'Keine Adresse eingegeben. Bitte machen Sie Angaben zu der Adresse, nach der Sie suchen.',
	'The geocode was successful but returned no results.' => 'Die Geokodierung war erfolgreich, führte jedoch zu keinen Suchergebnissen.',
	'You are over your quota. If this is a shared server, enable <a href="{url}">Google Maps API Keys.</a>' => 'Sie haben Ihr Kontingent an Abfragen überschritten. Falls Sie einen gemeinsam genutzten Server benutzen, aktivieren Sie <a href="{url}">Google Maps API Keys.</a>',
	'Your request was denied for some reason.' => 'Ihre Anfrage wurde aus unbestimmten Gründen verweigert.', // TODO: "indefinite reasons"
	//'Invalid request. Please provide more address information.' => 'X', // TODO: not translated
	// 'Response from Google Maps API:' => 'X',
	// 'Failed to execute cURL command.' => 'X',
	// 'Unknown cURL response:' => 'X',

	// Settings page
	'Documentation & Feedback' => 'Dokumentation & Feedback',
	'You can reach us at {supportEmail}. All questions, comments, and suggestions are welcome!' => 'Sie erreichen uns unter {supportEmail}. Fragen, Kommentare oder Vorschläge nehmen wir gerne entgegen!',
	'Google Maps API Keys' => 'Google Maps API Keys', // TODO: not translated :)
	'Using API keys is optional, but you may find it beneficial in many situations:' => 'Das Verwenden von API Keys ist optional, kann aber in verschiedenen Situationen von Vorteil sein:', // TODO: "various situations"
	'If your website is on a shared server, or' => 'Ihre Webseite befindet sich auf einem gemeinsam genutzten Server, oder',
	'If you expect extremely high traffic to your website.' => 'Sie rechnen mit sehr hohem Besucheraufkommen auf Ihrer Seite.',
	'To get your Google API keys, <a href="{url}" target="_blank">follow these instructions...</a>' => 'Eine Anleitung zum Erstellen von Google API Keys <a href="{url}" target="_blank">finden Sie hier</a>.', // TODO: "create/generate keys"
	'Google API Server Key' => 'Google API Server Key', // TODO: not translated
	'Used for address lookups' => 'Wird für Adressabfragen benutzt', // TODO: no period?
	'Google API Browser Key' => 'Google API Browser Key', // TODO: not translated
	'Used for static & dynamic map rendering' => 'Wird für das Rendern der statischen und dynamischen Karten benutzt', // TODO: no period?
	'Geolocation' => 'Geolokalisation',
	'Depending on the purpose of your website, you may need to use <strong>IP detection</strong> and <strong>geolocation</strong> to automatically detect where your site visitors are located.' => 'Abhängig vom Einsatzzweck Ihrer Webseite, könnte es nötig sein über <strong>IP-Adressen-Ermittlung</strong> und <strong>Geolokalisation</strong> den momentanen Aufenthaltsort Ihrer Benutzer automatisch zu ermitteln.',
	'Select a service...' => 'Auswahl des Anbieters...',
	'Geolocation is disabled.' => 'Geolokalisation ist deaktiviert.',
	'A free service, FreeGeoIp.net can perform IP address lookups automatically.' => 'Ein kostenloser Anbieter, über den Geolokalisierungs-Abfragen über IP-Adressen möglich sind.', // TODO: this means "geolocation lookups" via IP address, right? And, is it ok to leave out "automatically"?
	'A paid subscription service, MaxMind provides more accurate results, with a greater guarantee of uptime.' => 'Ein kostenpflichtiger Anbieter, der akkuratere Ergebnisse liefert und eine bessere Verfügbarkeit garantieren kann.', // TODO: left out the service's name, cause in german it's easily possible to build the sentence with präpositions instead.
	'MaxMind Configuration' => 'MaxMind Einstellungen',
	'<strong>Step 1:</strong> Subscribe to a <a href="{url}" target="_blank">Web Service...</a>' => '<strong>Schritt 1:</strong> Melden Sie sich zu einem der <a href="{url}" target="_blank">angebotenen Web Dienste</a> an...',
	'<strong>Step 2:</strong> Which <a href="{url}" target="_blank">Web Service</a> have you subscribed to?' => '<strong>Schritt 2:</strong> Zu welchem <a href="{url}" target="_blank">Web Dienst</a> sind Sie angemeldet?',
	'<strong>Step 3:</strong> Copy your <a href="{url}" target="_blank">security access information</a> and paste it here...' => '<strong>Schritt 3:</strong> Kopieren Sie Ihre <a href="{url}" target="_blank">Zugangsdaten</a> und fügen sie hier ein...',
	'(service disabled)' => '(Dienst deaktiviert)',
	'User ID' => 'Benutzer ID',
	'License Key' => 'Lizenz Key',
	'If you have recently switched services, clear old geolocation results by visiting:' => 'Nach dem Wechsel des Anbieters können Sie hier Ihre bisherigen Geolokalisierungs-Daten löschen:', // TODO: "geolocation data"
	'WARNING' => 'WARNUNG',
	'FreeGeoIp.net may experience unexpected downtime, <u>which could have a negative impact on your website!</u> It is highly recommended <strong>not</strong> to rely on FreeGeoIp.net.' => 'Bei FreeGeoIp.net könnte möglicherweise mit unerwarteten Ausfallzeiten zu rechnen sein, <u>die negative Einflüsse auf Ihre Webseite haben!</u> Es wird dringend empfohlen, sich <strong>nicht</strong> auf FreeGeoIp.net zu verlassen.', // TODO: remove "möglicherweise" in case FreeGeoIp.net is really bad :D
	'<strong>If you require geolocation, MaxMind is a far more reliable service.</strong>' => '<strong>MaxMind ist der weitaus zuverlässigere Anbieter. Bitte beachten Sie dies, falls Sie auf Geolokalisation angewiesen sind.</strong>',
	'Plugin Debug Page' => 'Plugin Debug Seite',
	'Sometimes you need to know a little more about what the plugin is doing. This special page can give you insight into the geolocation capabilities of your site.' => 'Es kann vorkommen, dass Sie etwas genauer wissen müssen, was das Plugin macht. Diese Seite ermöglicht Ihnen Einblicke in die Möglichkeiten zur Geolokalisation mit Ihrer Seite.',

);