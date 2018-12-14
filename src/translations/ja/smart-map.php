<?php

return [

    // Plugin title
    'Smart Map' => 'スマートマップ',

    // Welcome page
    // 'Welcome to Smart Map!' => 'X',
    // 'Thanks for using Smart Map!' => 'X',
    // 'Feel free to <a href="{emailLink}" target="_blank">contact us</a> with any feature requests, bug reports, or suggestions.' => 'X <a href="{emailLink}" target="_blank">X</a>',
    // 'Create an Address field' => 'X',
    // 'View full documentation' => 'X',

    // Fieldtype configuration
    'Address' => '住所',
    'Address Field Layout' => 'アドレスフィールドの設計',
    'Manage how the subfields will be arranged.' => 'サブフィールドが配置される方法を管理します。',
    'Label' => 'ラベル',

    // Address field
    'Street Address' => '住所',
    'Apartment or Suite' => 'アパートやスイート',
    'City' => 'シティ',
    'State' => '州または県',
    'Zip Code' => '郵便番号',
    'Country' => '国',
    'Latitude' => '緯度',
    'Longitude' => '経度',
    'Search Addresses' => '検索アドレス',
    'Drag Pin' => 'ピンをドラッグ',

    // Search results modal
    'Showing 1 search result...' => '1検索結果を表示',
    'Showing {total} search results...' => '{total}の検索結果を表示',

    // Error messages
    'No address provided. Please enter a partial address to search for.' => 'アドレスが入力されていません。検索対象の部分アドレスを入力してください。',
    'The geocode was successful but returned no results.' => 'ジオコードは成功したが、何も結果が返されません',
    'You are over your quota. If this is a shared server, enable <a href="{url}">Google Maps API Keys.</a>' => '最大割り当てが達成されています。このサーバを共有している場合は、<a href="{url}">GoogleマップのAPIキー</a>を有効にしてください。',
    'Your request was denied for some reason.' => '残念ながら、あなたの要求は拒否されました。',
    'Invalid request. Please provide more address information.' => 'クエリが欠落しています。',
    // 'Response from Google Maps API:' => 'X',
    // 'Failed to execute cURL command.' => 'X',
    // 'Unknown cURL response:' => 'X',

    // Address field settings
    // 'Set defaults for "Drag Pin" modal map' => 'X',
    // 'When specifying a location via the "Drag Pin" modal, you may set default coordinates and zoom level.' => 'X',
    // 'Set a default pin location for modal?' => 'X',
    // 'Zoom' => 'X',

    // Settings page
    'Documentation & Feedback' => 'ドキュメント＆フィードバック',
    // 'All documentation can be found at <a href="{url}" target="_blank">doublesecretagency.com</a>.' => 'X <a href="{url}" target="_blank">X</a>',
    'You can reach us at {supportEmail}. All questions, comments, and suggestions are welcome!' => '{supportEmail}までご連絡ください。ご質問、コメント、提案を歓迎します！',

    'Google Maps API Keys' => 'Google Maps API Keys',
    // 'As of June 11, 2018, all Google Maps API requests must include an API key. <strong>Keyless access is no longer supported.</strong>' => 'X <strong>X</strong>',
    'To get your Google API keys, <a href="{url}" target="_blank">follow these instructions...</a>' => 'GoogleのAPIキーを取得するには、<a href="{url}" target="_blank">次の手順に従い...</a>',
    'Google API Server Key' => 'GoogleのAPIサーバキー',
    'Used for address lookups' => 'アドレス参照に使用されます',
    'Google API Browser Key' => 'GoogleのAPIブラウザキー',
    'Used for static & dynamic map rendering' => 'スタティック＆ダイナミックマップのレンダリングに使用されます',

    'Geolocation' => 'ジオロケーション',
    'Depending on the purpose of your website, you may need to use <strong>IP detection</strong> and <strong>geolocation</strong> to automatically detect where your site visitors are located.' => 'ウェブサイトの使用状況に応じて、あなたのサイトの訪問者がどこにあるかを自動的に検出するために、<strong>IP検出</strong>と<strong>ジオロケーション</strong>を使用する必要があります。',
    'Select a service...' => 'サービスを選択してください...',
    'Geolocation is disabled.' => 'ジオロケーションが有効になっていません。',
    // 'A free service, ipstack can perform IP address lookups automatically. Formerly known as FreeGeoIp.net.' => 'X',
    'A paid subscription service, MaxMind provides more accurate results, with a greater guarantee of uptime.' => 'あなたは、サービスのMaxmindを支払うことができます。MaxMindは、稼働時間のより大きな保証と、より正確な結果を提供します。',

    // 'ipstack Configuration' => 'X',
    // 'Copy your <a href="{url}" target="_blank">API Access Key</a> and paste it here...' => 'X <a href="{url}" target="_blank">X</a>',

    'MaxMind Configuration' => 'MaxMindを構成します',
    '<strong>Step 1:</strong> Subscribe to a <a href="{url}" target="_blank">Web Service...</a>' => '<strong>ステップ1</strong><a href="{url}">Webサービス</a>を購読します...',
    '<strong>Step 2:</strong> Which <a href="{url}" target="_blank">Web Service</a> have you subscribed to?' => '<strong>ステップ2</strong>どの<a href="{url}">Webサービス</a>あなたがを購読していますか？',
    '<strong>Step 3:</strong> Copy your <a href="{url}" target="_blank">security access information</a> and paste it here...' => '<strong>ステップ3</strong><a href="{url}">セキュリティアクセス情報</a>をコピーし、それをここに貼り付け...',
    '(service disabled)' => '（このサービスは無効）',
    'User ID' => 'ユーザーID',
    'License Key' => 'ライセンスキー',
    'If you have recently switched services, clear old geolocation results by visiting:' => 'あなたは最近、にアクセスしてサービスを、明確な古いジオロケーション結果を切り替えた場合。',

    'Plugin Debug Page' => 'プラグインのデバッグページ',
    'Sometimes you need to know a little more about what the plugin is doing. This special page can give you insight into the geolocation capabilities of your site.' => '時には、プラグインの能力について少し詳細を知る必要があります。このページには、あなたのウェブサイトの地理位置能力をお知らせします。',

    // 'Loading map...' => 'X'

];
