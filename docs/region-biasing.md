---
description: Sometimes your location lookups won't properly match the expected target. This is often because the target shares a similar name as a more well-known area.
---

# Region Biasing

Sometimes your location lookups won't properly match the expected target. This is generally because the specified target shares a similar name as a more well-known area.

### Example:

You have visitors searching for "venice" (the city in California). But there's a more famous Venice in the world (the city in Italy), and therefore the Italian city takes precedence.

You can specify which region to hone in on by using the `components` option...

```twig
{% set target = "Venice" %}

{% set params = {
    'target': target,
    'range': 100,
    'components': {
        'country': 'US',
        'administrative_area': 'California',
    },
} %}

{% set entries = craft.entries.myFieldHandle(params).order('distance').all() %}
```

You can use the following parameters for components:

 - `route`
 - `locality`
 - `administrative_area`
 - `postal_code`
 - `country`

Read the official Google docs on [Component Filtering...](https://developers.google.com/maps/documentation/geocoding/overview#component-filtering)
