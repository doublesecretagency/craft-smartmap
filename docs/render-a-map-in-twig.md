---
description: Create a map with either a single marker or a multiple markers. The map can be either dynamic or static.
---

# Render a map in Twig

<update-message/>

## How to draw a map using Twig

Create a **dynamic** or **static** map with one or more markers on it...

<img :src="$withBase('/images/smartmap-map-example.png')" class="dropshadow" alt="">

## Dynamic map

```twig
{{ craft.smartMap.map(locations, options) }}
```

A dynamic Google map, capable of being completely customized either **before** it's rendered (via the `options` parameter) or **after** it's rendered (via ordinary front-end JavaScript).

## Static map

```twig
{{ craft.smartMap.img(locations, options) }}
```

A static Google map. A static map can only be customized **before** it's rendered (via the `options` parameter).

If needed, you can also directly get only the URL for a static map:

```twig
{{ craft.smartMap.imgSrc(locations, options) }}
```

## Parameters

| Parameter   | Description
|:------------|:------------
| `locations` | An individual address or element, or an array of elements. (see below)
| `options`   | [View the full list of options.](/customizing-the-map-in-twig/) The options value must be an object, a set of key/value pairs.

The `locations` value can be in any of the following formats:

| `locations` Format       | Example
|:-------------------------|:--------
| As an individual field   | `entry.address`
| As an individual element | `craft.entries.slug('my-entry').one()`
| As an array of elements  | `craft.entries.section('myLocations').all()`
| As an Element Query      | `craft.entries.section('myLocations')`

:::warning Default dimensions of static maps
Since a static map can't dynamically calculate a width or height, the image will be set to 200px by 200px by default. This can be easily overridden in the `options` parameter.
:::
