---
description: You can quickly generate a Google Maps link to your location. Similarly, you can generate a "directions to this location" URL.
---

# Linking to a separate Google Map page

## Generating a link to a Google map page

```twig
<a href="{{ entry.myFieldHandle.googleMapUrl }}">Open in Google Maps</a>
```

## Generating a link for directions

```twig
{# Set destination with no starting point #}
<a href="{{ entry.myFieldHandle.directionsUrl }}">Directions in Google Maps</a>

{# Automatically detect user's starting location #}
<a href="{{ entry.myFieldHandle.directionsUrl }}&amp;saddr=Current+Location">Directions in Google Maps</a>
```

The `directionsUrl` method has up to three optional parameters:

```twig
directionsUrl(destinationTitle, startingTitle, startingAddress)
```

| Parameter          | Description
|:-------------------|:------------
| `destinationTitle` | Title of the destination marker.
| `startingTitle`    | Title of the starting marker.
| `startingAddress`  | An instance of another Address model. A path will be calculated between the two locations.

:::warning Titles may not always appear
Depending on the browser and usage, the `destinationTitle` and `startingTitle` may or may not be utilized.
:::
