---
description: Creating a map from a Matrix field is easy.
---

# How to use with a Matrix field

Creating a map from a Matrix field is easy... If your Matrix field handle is `myMatrixField`, then your code would look something like this:

```twig
{% set entry = craft.entries.slug('my-awesome-entry').one() %}

{% set locations = entry.myMatrixField %}

{% set options = {
    height: 300,
    zoom: 3
} %}

{{ craft.smartMap.map(locations, options) }}
```

If you want to use only a single Matrix block, you can call a specific item from your `locations` array:

```twig
{{ craft.smartMap.map(locations[0], options) }}
```
