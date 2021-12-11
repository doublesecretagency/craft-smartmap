---
description: Create a new Address field, and add it to anything you want. Your new field can be part of any Element Type. (Entry, Asset, User, etc)
---

# Using an Address field

<update-message/>

Create a new **Address** field, and add it to anything you want. Your new field can be part of an Entry, Asset, User, etc... basically, any Element Type is a valid host for your new address field. You can set an address as a Global field, or even include it in a Matrix field!

When you're editing your content, you'll have three methods available to you for modifying an address:

 - Type it in manually (you can enter anything you want).
 - Click "Search Addresses" to find similar matches around the world.
 - Click "Drag Pin" to manually place a pin anywhere on the map.

If you opt to manually drag the pin, the default position will be whatever is currently populating the Latitude & Longitude fields. This is really helpful when you want to "Search Addresses" for the closest physical address, then follow up with "Drag Pin" to mark an even more precise location.

## Displaying your field values in a Twig template

Your address field will produce an Address Model, which contains the following values:

| Handle    | Type         | Example
|:----------|:-------------|:--------
| `street1` | _string_     | 350 5th Ave
| `street2` | _string_     | Unit #101
| `city`    | _string_     | New York
| `state`   | _string_     | NY
| `zip`     | _string_     | 10118
| `country` | _string_     | United States
| `lat`     | _float_      | `40.7482436`
| `lng`     | _float_      | `-73.9851073`
| `coords`  | `[lat, lng]` | `[40.7482436, -73.9851073]`

So for example, if the handle of your Address field is "address", you would display the data like this:

```twig
{% set entry = craft.entries.slug('my-entry').one() %}

<h1>{{ entry.title }}</h1>
<div>
    {{ entry.address.street1 }}<br />
    {{ entry.address.street2 }}<br />
    {{ entry.address.city }}, {{ entry.address.state }} {{ entry.address.zip }}
</div>
<div>
    Latitude: {{ entry.address.lat }}<br />
    Longitude: {{ entry.address.lng }}
</div>
```

The code above will render an address like this:

<img :src="$withBase('/images/smartmap-template-example.png')" class="dropshadow" alt="">
