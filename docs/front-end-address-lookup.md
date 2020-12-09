---
description: You may find yourself needing to lookup an address or zip code from the front-end. You can use the same geocoding techniques that Smart Map is using.
---

# Front-End Address Lookup

You may find yourself needing to lookup an address or zip code from the front-end of your website. Fortunately, you can use the same geocoding techniques that Smart Map is using, without having to write a lot of extra custom code.

## Lookup via Variable

If you already know what target address you're looking for when the page loads, you can conduct the lookup through a Twig variable...

```twig
{% set target = 90001 %}

{% set response = craft.smartMap.lookup(target) %}

<pre>{{ dump(response) }}</pre>
```

## Lookup Coordinates

This behaves much like `lookup`, but returns **only the coordinates** of **only the first matching result...**

```twig
{% set target = 90001 %}

{% set coords = craft.smartMap.lookupCoords(target) %}

<p>Coordinates: {{ coords.lat }}, {{ coords.lng }}</p>
```

## Lookup via AJAX

You may need your users to lookup an address without loading a new page. In that case, you'd use **AJAX** to retrieve a list of address matches.

:::tip Example uses jQuery
This example uses jQuery to perform the AJAX call, however jQuery is not required.
:::

```twig
{% js %}

    var target = 90001;
    var data   = {target: target};

    var csrfTokenName  = "{{ craft.app.config.general.csrfTokenName }}";
    var csrfTokenValue = "{{ craft.app.request.csrfToken }}";

    data[csrfTokenName] = csrfTokenValue; // Append CSRF Token to outgoing data

    $.post('/actions/smart-map/lookup', data, function(response) {
        console.log(response);
    });

{% endjs %}
```

You can see how the `response` data is formatted in the [Google Maps documentation...](https://developers.google.com/maps/documentation/geocoding/start#sample-request)

:::warning Multiple Results
More often than not, your lookup will actually contain multiple matches in your query response. It's up to you to decide how you want to parse that out, in order to get the exact data you want to retrieve.
:::

## Mimicking the CP "Address" field on the Front-End

If you're attempting to recreate the entire UX of the Control Panel's "Address" field, this will only get you a part of the way there. It will be up to you to decide how (or whether) to display a list of multiple results, letting the end-user select which address is the best possible match.
