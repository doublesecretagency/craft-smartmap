---
description: It's possible to use KML files with Smart Map. You can either apply a KML file to an existing map, or generate a new map on the fly.
---

# KML files

<update-message/>

When the needs of your map get too complex, you may find it beneficial to create a custom map with [Google My Maps](https://www.google.com/maps/about/mymaps/) (or a similar service). Once you have your KML file in hand, there are two ways to apply it using Smart Map...

## Generate a new map from the KML file

You can use the `kml` function to generate a new map from scratch, simply pass in the KML asset and options. The options are identical to the list of standard [map options](/customizing-the-map-in-twig/).

```twig
kml(kmlFile, options)
```

### Example:

```twig
{% set kmlFile = entry.kmlFile.one() %}
{% set options = {
    height: 400,
    center: {'lat':34.0522342, 'lng':-118.2436849}
} %}

{% if kmlFile %}
    {{ craft.smartMap.kml(kmlFile, options) }}
{% endif %}
```

## Apply the KML file to an existing map

If you'd like to apply the KML file to an _existing_ map, use `kmlLayer`. You can pass in the KML file, and the map ID of your target map.

```twig
kmlLayer(kmlFile, mapId)
```

### Example:

```twig
{% set kmlFile = entry.kmlFile.one() %}

{% if kmlFile %}
    {{ craft.smartMap.kmlLayer(kmlFile, 'smartmap-mapcanvas-1') }}
{% endif %}
```

:::warning The KML file must be publicly accessible
Because of the way Google Maps applies the KML layer, the KML file must be accessible from a public website.

If you are testing this feature locally, the KML file may refuse to load.
:::
