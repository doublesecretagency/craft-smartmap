module.exports = {
    markdown: {
        anchor: { level: [2, 3] },
        extendMarkdown(md) {
            let markup = require('vuepress-theme-craftdocs/markup');
            md.use(markup);
        },
    },
    base: '/smart-map/',
    title: 'Smart Map plugin for Craft CMS',
    plugins: [
        [
            'vuepress-plugin-clean-urls',
            {
                normalSuffix: '/',
                indexSuffix: '/',
                notFoundPath: '/404.html',
            },
        ],
    ],
    theme: 'craftdocs',
    themeConfig: {
        codeLanguages: {
            php: 'PHP',
            twig: 'Twig',
            js: 'JavaScript',
        },
        logo: '/images/icon.svg',
        searchMaxSuggestions: 10,
        nav: [
            {
                text: 'How It Works',
                items: [
                    {text: 'Using an Address field', link: '/using-an-address-field/'},
                    {text: 'Render a map in Twig', link: '/render-a-map-in-twig/'},
                    {text: 'Sorting entries by closest locations', link: '/sorting-entries-by-closest-locations/'},
                    {text: 'Filtering entries by subfield value', link: '/filtering-entries-by-subfield-value/'},
                    {text: 'Filtering out entries with invalid coordinates', link: '/filtering-out-entries-with-invalid-coordinates/'},
                    {text: 'Using a filter fallback in proximity searches', link: '/using-a-filter-fallback-in-proximity-searches/'},
                    {text: 'Region Biasing', link: '/region-biasing/'},
                    {text: 'Internationalization Support', link: '/internationalization-support/'},
                    {text: 'Customizing the map in Twig', link: '/customizing-the-map-in-twig/'},
                    {text: 'Manipulating the map in JavaScript', link: '/manipulating-the-map-in-javascript/'},
                    {text: 'Styling a Map', link: '/styling-a-map/'},
                    {text: 'KML files', link: '/kml-files/'},
                    {text: 'Adding marker info bubbles', link: '/adding-marker-info-bubbles/'},
                    {text: 'Different icons for different marker types', link: '/different-icons-for-different-marker-types/'},
                    {text: 'Linking to a separate Google Map page', link: '/linking-to-a-separate-google-map-page/'},
                    {text: 'How to use with a Matrix field', link: '/how-to-use-with-a-matrix-field/'},
                    {text: 'Automatically format an entire address', link: '/automatically-format-an-entire-address/'},
                    {text: '"isEmpty" and "hasCoords"', link: '/isempty-and-hascoords/'},
                    {text: 'Front-End Address Lookup', link: '/front-end-address-lookup/'},
                    {text: 'Front-End Entry Form', link: '/front-end-entry-form/'},
                    {text: 'Importing Addresses', link: '/importing-addresses/'},
                    {text: 'Exporting the Address data', link: '/exporting-the-address-data/'},
                    {text: 'Get Google API keys', link: '/get-google-api-keys/'},
                    {text: 'Override Google API keys', link: '/override-google-api-keys/'},
                    {text: 'Visitor Geolocation', link: '/visitor-geolocation/'},
                    {text: 'Map Debug Page', link: '/map-debug-page/'},
                    {text: 'Troubleshooting', link: '/troubleshooting/'},
                ]
            },
            {
                text: 'More',
                items: [
                    {text: 'Double Secret Agency', link: 'https://www.doublesecretagency.com/plugins'},
                    {text: 'Our other Craft plugins', link: 'https://plugins.doublesecretagency.com', target:'_self'},
                ]
            },
        ],
        sidebar: {
            '/': [
                'using-an-address-field',
                'render-a-map-in-twig',
                'sorting-entries-by-closest-locations',
                'filtering-entries-by-subfield-value',
                'filtering-out-entries-with-invalid-coordinates',
                'using-a-filter-fallback-in-proximity-searches',
                'region-biasing',
                'internationalization-support',
                'customizing-the-map-in-twig',
                'manipulating-the-map-in-javascript',
                'styling-a-map',
                'kml-files',
                'adding-marker-info-bubbles',
                'different-icons-for-different-marker-types',
                'linking-to-a-separate-google-map-page',
                'how-to-use-with-a-matrix-field',
                'automatically-format-an-entire-address',
                'isempty-and-hascoords',
                'front-end-address-lookup',
                'front-end-entry-form',
                'importing-addresses',
                'exporting-the-address-data',
                'get-google-api-keys',
                'override-google-api-keys',
                'visitor-geolocation',
                'map-debug-page',
                'troubleshooting',
            ],
        }
    }
};
