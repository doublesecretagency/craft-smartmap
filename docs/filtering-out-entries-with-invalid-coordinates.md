---
description: You can filter out location results which do not have valid coordinates.
---

# Filtering out entries with invalid coordinates

<update-message/>

You can pass a `hasCoords` parameter to **show only locations with valid coordinates...**

```twig
{% set params = {
    hasCoords: true
} %}

{% set entries = craft.entries.myFieldHandle(params).all() %}
```

The resulting entries will exclude any locations that did not have a valid set of coordinates. Coordinates are only valid if both the **latitude** and **longitude** values are populated.

### PHP

This can also be done in PHP...

```php
use craft\elements\Entry;

$entries = Entry::find()
    ->section('mySection')
    ->myFieldHandle([
        'hasCoords' => true
    ])
    ->all();
```
