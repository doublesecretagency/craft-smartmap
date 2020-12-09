---
description: After the map has been rendered in the browser, you can continue to make adjustments via JavaScript. The Smart Map tools are in a JS object called "smartMap".
---

# Manipulating the map in JavaScript

After the map has been rendered in the browser, you can then continue to make adjustments via JavaScript. The Smart Map tools are in a JS object called `smartMap`... this is also where all of the maps & markers are stored.

:::warning Google Maps API
The map, marker, and info window JS objects are exactly the same as if this were a direct implementation of Google Maps. You can do whatever you want within the context of the [Google Maps API](https://developers.google.com/maps/documentation/javascript/reference).
:::

## Map Object

The array key for your map is the map's `id`. If you didn't [manually override the `id`](/customizing-the-map-in-twig/#map-options), then by default it will be set to "smartmap-mapcanvas-1".

```js
smartMap.map['smartmap-mapcanvas-1']
```

This is a Google Maps [Map object](https://developers.google.com/maps/documentation/javascript/reference/map). The `smartMap.map` array is a collection of `google.maps.Map` objects. Anything that can be done with a Map object can also be done with a `smartMap.map` object.

```js
// Style your map
smartMap.map['smartmap-mapcanvas-1'].setOptions({styles: styles});
```

## Marker Object

The array key for your marker is composed like this:

 - **Map ID:** See Map Object (above)
 - **Element ID:** The id of the marker's parent element.
 - **Field Handle:** The handle of the address field.

```js
smartMap.marker['<MAP ID>.<ELEMENT ID>.<FIELD HANDLE>']

// For example...
smartMap.marker['smartmap-mapcanvas-1.33.myAddressField']
```

This is a Google Maps [Marker object](https://developers.google.com/maps/documentation/javascript/reference/marker). The `smartMap.marker` array is a collection of `google.maps.Marker` objects. Anything that can be done with a Marker object can also be done with a `smartMap.marker` object.

```js
// Make a marker draggable
smartMap.marker['smartmap-mapcanvas-1.33.myAddressField'].setDraggable(true);
```

## Info Window Object

The array key for your info window is composed exactly like the Marker Object (see above).

```js
smartMap.infoWindow['smartmap-mapcanvas-1.33.myAddressField']
```

This is a Google Maps [InfoWindow object](https://developers.google.com/maps/documentation/javascript/reference/info-window). The `smartMap.infoWindow` array is a collection of `google.maps.InfoWindow` objects. Anything that can be done with an InfoWindow object can also be done with a `smartMap.infoWindow` object.

```js
// Change info window content
var infoWindow = smartMap.infoWindow['smartmap-mapcanvas-1.33.myAddressField'];
infoWindow.setContent('<h2>New Info</h2>');
```

## Additional Functions

```js
// Get list of rendered maps
smartMap.listMaps();

// Get list of rendered markers
smartMap.listMarkers();

// Get list of rendered marker info windows
smartMap.listInfoWindows();
```

```js
// Create a new map
smartMap.createMap(mapId, options);

// Create a new marker
smartMap.createMarker(markerName, options);

// Delete an existing marker
smartMap.deleteMarker(markerName);

// Create a new marker info window
smartMap.createInfoWindow(markerName, options);
```

```js
// Refresh a map
smartMap.refreshMap(mapId);
```
