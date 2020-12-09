---
description: To make Twig logic a little easier, the Address model includes the "isEmpty" and "hasCoords" methods.
---

# `isEmpty` and `hasCoords`

To make Twig logic a little easier, the Address model includes two methods...

## `.isEmpty`

You can easily see if any parts of an address exist by using `isEmpty`:

```twig
{% if not entry.myAddressField.isEmpty %}
    {{ entry.myAddressField.format }}
{% endif %}
```

The `isEmpty` detection does not include coordinates... It only cares if any of the **address** fields have been completed.

## `.hasCoords`

To check if a valid coordinate set exists, you can use `hasCoords`:

```twig
{% if entry.myAddressField.hasCoords %}
    Latitude:  {{ entry.myAddressField.lat }}<br>
    Longitude: {{ entry.myAddressField.lng }}
{% endif %}
```

The `hasCoords` method ensures that both coordinates exist, and are numeric.
