---
description: Conduct a proximity search to find the nearest location.
---

# Sorting entries by closest locations

<update-message/>

## Twig template

Include this in your `craft.entries` call:

```twig
.myFieldHandle(params).orderBy('distance')
```

This will tell Craft to:

 - Filter your Address field with the parameters you specify.
 - Order the results by closest distance from your specified target.

```twig
{# example.com/search?near=90210 #}

{% set target = craft.request.getParam('near') %}
{% set params = {
    target: target,
    range: 100
} %}

{% set entries = craft.entries.myFieldHandle(params).orderBy('distance').all() %}

<h1>Showing results for "{{ target }}"...</h1>
{% for entry in entries %}
    <h2>{{ entry.title }}</h2>
    <div>
        {{ entry.myFieldHandle.street1 }}<br />
        {{ entry.myFieldHandle.street2 }}<br />
        {{ entry.myFieldHandle.city }}, {{ entry.myFieldHandle.state }} {{ entry.myFieldHandle.zip }}<br>
        <strong>{{ entry.myFieldHandle.distance | number_format(1) }} miles away</strong>
    </div>
{% else %}
    <h2>No results found</h2>
{% endfor %}
```

Your target can be anything that translates into a full or partial address...

 - 90210
 - Aurora, IL
 - 742 Evergreen Terrace

Or you can even use specific coordinates (lat,lng) as your target...

 - 38.897659,-77.036587

**All parameters are optional.**

| Parameter | Default         | Description
|:----------|:----------------|:------------
| target    | _(autodetects)_ | Starting point for proximity search
| range     | `25`            | Search radius, measured in `units`
| units     | `miles`         | Units of measurement (`miles` or `kilometers`)

## PHP in a separate plugin or module

This operates very similarly to how it's done in Twig:

```php
$entries = Entry::find()
    ->myFieldHandle([
        'target' => 90210,
        'range' => 100
    ])
    ->orderBy('distance')
    ->all();
```
