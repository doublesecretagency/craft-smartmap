---
description: By applying a filter, you can screen the subfields of your address fields. This ensures that only entries with an exact match are returned in the results.
---

# Filtering entries by subfield value

By applying a `filter`, you can screen the subfields of your address fields. This allows you to ensure that only entries with an _exact match_ are returned in the results.

```twig
{# Guarantee all results are within the United States #}
{% set params = {
    filter: {
        country: 'United States'
    }
} %}

{% set entries = craft.entries.myFieldHandle(params).all() %}
```

The `filter` can be applied in tandem with other parameters (like `target` and `range`). Which means that you can conduct a normal [proximity search](/sorting-entries-by-closest-locations/), and _also_ filter the results by a specific subfield.

```twig
{# Get closest location, which MUST be in Beverly Hills #}
{% set params = {
    target: '90210',
    range: 50,
    filter: {
        city: 'Beverly Hills'
    }
} %}
```

Here are a few other acceptable variations...

```twig
{# Filter by multiple subfields simultaneously #}
{% set params = {
    filter: {
        city: 'Springfield',
        state: 'OH'
    }
} %}

{# Allow multiple potential values for a subfield #}
{% set params = {
    filter: {
        country: ['United Kingdom', 'Germany', 'France']
    }
} %}
```

:::warning Fallback Filter
It's possible to use the `filter` as a [fallback mechanism](/using-a-filter-fallback-in-proximity-searches/) for proximity searches. So if the geocoding results are too broad, it will run a follow-up filter to see if there are any direct matches to the specified `target` value.
:::

### PHP

Everything described above in Twig can also be done via PHP.

```php
use craft\elements\Entry;

$entries = Entry::find()
    ->section('mySection')
    ->myFieldHandle([
        'filter' => [
            'country' => 'United States'
        ]
    ])
    ->all();
```
