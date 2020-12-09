---
description: You can customize your map before rendering it by simply passing in an "options" parameter. The options value must be an object, a set of key/value pairs.
---

# Customizing the map in Twig

:::warning Use JS for styles
Need to apply `styles` to your map? [Use JavaScript instead...](/styling-a-map/)

Due to the inherent complexity of map styles, that value should be applied directly in JS.
:::

You can customize your map before rendering it by simply passing in an `options` parameter. The options value must be an **object**, a set of key/value pairs:

```twig
{% set options = {
    height: 300,
    zoom: 4,
    draggable: false,
    markerInfo: '_includes/mapInfoBubble',
    markerOptions: {
        icon: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
    },
    infoWindowOptions: {
        maxWidth: 200
    }
} %}

{{ craft.smartMap.map(locations, options) }}
```

Here's the magical part... You can pass in corresponding options defined in the [Google Maps API:](https://developers.google.com/maps/documentation/javascript/reference)

## Map Options

Standard options from [google.maps.MapOptions](https://developers.google.com/maps/documentation/javascript/reference#MapOptions) can be set in the root of your `options` object, alongside any custom options like `id`, `width` and `height`...

```twig
{% set options = {
    height: 300,
    mapTypeId: 'google.maps.MapTypeId.HYBRID'
} %}
```

| Options             | Type     | Default            | Description
|:--------------------|:---------|:-------------------|-------------
| `id`                | _string_ | `"smartmap-mapcanvas-1"` | Set id attribute of container
| `width`             | _int_    | _null_             | Width of map (in px)
| `height`            | _int_    | _null_             | Height of map (in px)
| `zoom`              | _int_    | (uses `fitBounds`) | Zoom level (1 - 16)
| `center`            | _array_  | (uses `fitBounds`) | Map center, ie: {'lat':38.897837, 'lng':-77.036512}
| `scrollwheel`       | _bool_   | _false_            | Whether scroll wheel will zoom map
| `markerInfo`        | _string_ | _null_             | Template path [(read more)](/adding-marker-info-bubbles/)
| `markerOptions`     | _object_ | _null_             | Accepts any [google.maps.MarkerOptions](https://developers.google.com/maps/documentation/javascript/reference#MarkerOptions) properties
| `infoWindowOptions` | _object_ | _null_             | Accepts any [google.maps.InfoWindowOptions](https://developers.google.com/maps/documentation/javascript/reference#InfoWindowOptions) properties
| `maptype`           | _string_ | `"roadmap"`        | Type of map ("roadmap", "satellite", "hybrid", "terrain")<br>(Applies to static maps only)
| `scale`             | _string_ | `2`                | `1` = Non-retina<br>`2` = Retina<br>(Applies to static maps only)
| `field`             | _string_ or _array_ | _null_  | Specific field(s) to be included on map

## Marker Options & Info Window Options

Custom marker options and info window options can be set using the `markerOptions` and `infoWindowOptions` parameters:

```twig
{% set options = {
    markerOptions: {
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
            scaledSize: 'new google.maps.Size(32,32)'
        }
    },
    infoWindowOptions: {
        maxWidth: 200
    }
} %}
```

 - **markerOptions** accepts any [google.maps.MarkerOptions](https://developers.google.com/maps/documentation/javascript/reference#MarkerOptions) properties
 - **infoWindowOptions** accepts any [google.maps.InfoWindowOptions](https://developers.google.com/maps/documentation/javascript/reference#InfoWindowOptions) properties

These settings will affect all markers and info windows on the map. You can also [customize after the map has been rendered...](/manipulating-the-map-in-javascript/)
