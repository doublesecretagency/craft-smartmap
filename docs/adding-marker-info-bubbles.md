---
description: It's easy to add info bubbles to your map markers! An info bubble will appear when you click on each marker.
---

# Adding marker info bubbles

<update-message/>

<img :src="$withBase('/images/smartmap-marker-info-example.png')" class="dropshadow" alt="">

It's easy to add info bubbles to your map markers! An info bubble will appear when you click on each marker.

```twig
{% set options = {
    'markerInfo': '_includes/infoBubble'
} %}
```

The value of `markerInfo` is the path to your info bubble Twig template. That template can be as simple as you want...

```twig
<h3>{{ element.title }}</h3>
<div>{{ element.myFieldHandle.street1 }}</div>
```

... or far more complex.

These variables are pre-defined in your info bubble template:

| Variable            | Type     | Description
|:--------------------|:---------|:------------
| `element`           | _object_ | Full element data
| `marker`            | _object_ | Data specific to this marker
| `marker.mapId`      | _string_ | The id attribute of the map container
| `marker.markerName` | _string_ | The unique name of this marker
| `marker.coords`     | _object_ | Coordinates of this marker
| `marker.coords.lat` | _float_  | Latitude
| `marker.coords.lng` | _float_  | Longitude

Since any element type can contain an Address field, it is referred to in the template as a generic `element`.

Here is a more complex template example...

```twig
{% set segments = craft.request.segments %}

{% set moreInfoUrl = '/'~segments[0]~'/'~segments[1]~'/'~element.slug~'/' %}
{% set directionsUrl = craft.smartMap.directions(element.myAddressField, element.title) %}

<h1>{{ element.title }}</h1>
<div>
    {# Your address fields #}
    {{ element.myAddressField.street1 }}<br>
    {{ element.myAddressField.city }}, {{ element.myAddressField.state }} {{ element.myAddressField.zip }}
</div>
<div>
    {# Your other custom fields #}
    {{ element.telephone }}<br>
    {{ element.hours }}
</div>
<div>
    {# "More Info" and "Get Directions" links #}
    <a href="{{ moreInfoUrl }}">More Info</a> | 
    <a href="{{ directionsUrl }}" target="_blank">Get Directions</a>
</div>
<div>
    {# Special "zoomOnMarker" JavaScript function #}
    <span onclick="smartMap.zoomOnMarker('{{ marker.mapId }}', '{{ marker.markerName }}', 15)">Zoom On Marker</span>
</div>
```

Almost snuck that one past you! There is a special JavaScript function built into the `smartMap` object which will allow you to zoom the map in on a particular marker.

```js
smartMap.zoomOnMarker(mapId, markerName, zoomLevel)
```

:::warning Marker Info Template Errors
In the event of Twig errors in your marker info template, the error will be rendered inside of the info bubble. This allows for you to more easily debug any problems that may be occurring.
:::

## Address field in a Matrix block

If your address field exists within a Matrix field, then the `element` variable will actually contain a Matrix Block model. Therefore, the Entry would be the `element.owner`.

```twig
{% set matrixBlock = element %}
{% set entry = element.owner %}
```
