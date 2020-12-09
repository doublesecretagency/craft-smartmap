---
description: Occasionally, you'll need to know where your site visitors are located. Smart Map provides an easy way to geolocate visitors based on their IP address.
---

# Visitor Geolocation

Occasionally, you'll need to know where your site visitors are located. Fortunately, Smart Map provides an easy way to geolocate visitors based on their IP address.

On the plugin's Settings page, select which geolocation service you'd like to use...

<img :src="$withBase('/images/smartmap-visitor-geolocation.png')" class="dropshadow" alt="">

Accessing visitor geolocation data via Twig:

```twig
{% set visitor = craft.smartMap.visitor %}
```

Accessing visitor geolocation data via PHP:

```php
$visitor = SmartMap::$plugin->smartMap->visitor;
```

Both of these methods will return an array containing various data of the visitor's approximate whereabouts.

The `visitor` array will contain the following keys:

 - `ip`
 - `city`
 - `state`
 - `zipcode`
 - `country`
 - `latitude`
 - `longitude`
 - `coords`

```twig
{% set visitor = craft.smartMap.visitor %}

Visitor IP: {{ visitor.ip }}
Visitor's country: {{ visitor.country }}
```

:::warning Accuracy
These techniques rely on calculating location from the user's IP address. Please be aware, this will rarely be 100% accurate. Generally speaking, you will end up with geolocation results which are within a few miles of the visitor's actual location, although occasionally they will be detected as much farther away.

A more precise method of visitor geolocation can be done using the **HTML 5 geolocation** feature. However, this will prompt the user to give your site permission to know their location, and it's possible (and common) for them to decline.
:::

:::tip Localhost (127.0.0.1)
If you are performing geolocation from your local machine, you may get an empty set of results. This is because your IP address is 127.0.0.1 (localhost), which isn't recognized as a real location in the outside world.
:::
