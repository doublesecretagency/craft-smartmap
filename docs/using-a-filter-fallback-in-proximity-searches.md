---
description: When conducting a proximity search, you may want to allow the "filter" values to be set automatically. This may give you a slightly improved search result.
---

# Using a filter fallback in proximity searches

<update-message/>

As of v3.3, it's now possible for the `filter` values to be set automatically.

```twig
{# Will ignore geocoding results
 # and filter by any subfield
 # that matches the target 
 #}
{% set params = {
    target: '90210',
    range: 50,
    filter: 'fallback'
} %}
```

When a user searches for a partial address, Google will perform an address lookup (aka geocoding) in order to determine the _center point_ of the user's proximity search. However, this can be problematic if the user does not enter a _specific_ address.

If the user has only entered a postal code or city name, Google will return coordinates for the _geographic center_ of that region. This can lead to some confusion, as the "closest" results may belong in a neighboring state or province (due to the oddly-shaped nature of most regions).

To solve this problem, we have introduced the **filter fallback** technique. This allows imprecise searches to automatically [filter the subfield values](/filtering-entries-by-subfield-value/) based on the user's search query.

Instead of setting the `filter` as an array, simply set it to be `"fallback"` instead...

```twig
filter: 'fallback'
```

If the Google geocoding results are too broad (ie: not a street address), the `target` value will be automatically applied as a filter against all subfields.
