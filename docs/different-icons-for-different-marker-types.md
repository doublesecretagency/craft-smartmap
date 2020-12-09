---
description: Occasionally, you want to display different marker icons based on some distinction between marker types. You can use the following pseudo-code to get started.
---

# Different icons for different marker types

Occasionally, you want to display **different marker icons** based on some distinction between marker types.

You can use the following pseudo-code as a basis for performing that task. It will need to be customized to suit your own unique purposes.

```twig
{# Render map normally #}
{{ craft.smartMap.map(locations, options) }}

{# Loop through location entries #}
{% for entry in locations %}

    {#
      Perform custom logic
      to determine which icon
      will be assigned to each entry
    #}
    {% switch entry.markerType %}
        {% case 'exampleA' %}
            {% set icon = 'path/to/icon-A.jpg' %}
        {% case 'exampleB' %}
            {% set icon = 'path/to/icon-B.jpg' %}
        {% default %}
            {% set icon = 'path/to/default-icon.jpg' %}
    {% endswitch %}

    {# JS: Change marker icon #}
    {% js %}

        var markerId = 'smartmap-mapcanvas-1.{{ entry.id }}.myAddressField';
        smartMap.marker[markerId].setOptions({'icon': '{{ icon }}'});

    {% endjs %}

{% endfor %}
```

:::warning JavaScript "markerId"
This example makes [some assumptions](/customizing-the-map-in-twig/) about `markerId`, which may vary in your actual code:

 - `smartmap-mapcanvas-1` is assumed to be the id of your map. If you haven't [specified it elsewhere](/customizing-the-map-in-twig/#map-options), this will be the default map name.
 - Change `myAddressField` to be the actual field handle of your Address field.
:::
