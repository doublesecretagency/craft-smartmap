---
description: You can bulk import addresses using either the "Feed Me" or "Import" plugin. Here's how to generate coordinates before running the import...
---

# Importing Addresses

In order to import Address data, use the [Feed Me](https://plugins.craftcms.com/feed-me) plugin.

Before you run the import, it's important that you **already have** the latitude & longitude in your original data. You'll be able to simply set latitude & longitude alongside the other address data, and import it all at once.

:::warning Do bulk lookup before importing
There is currently no mechanism within Smart Map for doing bulk lookups. It's highly recommended that you include the coordinates in the initial import.
:::

## Bulk lookups on a CSV file

Here are a few recommended services for doing a bulk lookup:

 - [SmartyStreets](https://smartystreets.com) - Really nice data and easy to use, but expensive.
 - [GPS Visualizer](https://www.gpsvisualizer.com/geocoder/) - Not as nice, but it’s free.
 - [Geocodio](https://www.geocod.io) - Nice and cheap.

Once you've got the latitudes & longitudes included in the CSV file, you can easily import all the data at once.
