---
description: To ensure you have full access to the Google APIs, generate your API keys and enter them into the Smart Map > Settings page.
---

# Get Google API keys

In order to use the Google Maps API, you will need to generate two distinct API keys. Follow the official instructions provided by Google in order to set up a **browser key** and **server key**.

:::warning Full Walkthrough
[https://developers.google.com/maps/gmp-get-started](https://developers.google.com/maps/gmp-get-started)
:::

## Authorize required services

Each of the following API services must be enabled for this plugin to function properly...

| API Service         | ⬇️     |      |
|:--------------------|:-------|------|
| Maps JavaScript API | [Enable Service](https://console.cloud.google.com/apis/library/maps-backend.googleapis.com) | [More Info](https://developers.google.com/maps/documentation/javascript/overview)
| Maps Static API     | [Enable Service](https://console.cloud.google.com/apis/library/static-maps-backend.googleapis.com) | [More Info](https://developers.google.com/maps/documentation/maps-static/overview)
| Geocoding API       | [Enable Service](https://console.cloud.google.com/apis/library/geocoding-backend.googleapis.com) | [More Info](https://developers.google.com/maps/documentation/geocoding/overview)

Please enable each of these APIs before moving on to create API keys.

:::tip Complete Google API Library
[https://console.cloud.google.com/apis/library](https://console.cloud.google.com/apis/library) _(login required)_
:::

## Create the API keys

Follow these authoritative instructions for creating Google API keys...

:::tip Get API Keys
[https://developers.google.com/maps/documentation/javascript/get-api-key](https://developers.google.com/maps/documentation/javascript/get-api-key)
:::

The information provided below is supplemental to Google's primary documentation.

## Two API keys

You will need to set up **two independent keys**. After you have created the first key, repeat the process to create a second key.

### The difference?

There is only one difference between the two keys. The value selected for **Application Restrictions** determines whether you are creating a _browser_ or _server_ key.

- If you set the restrictions to "HTTP referrers", you are creating a **browser key**.
- If you set the restrictions to "IP addresses", you are creating a **server key**.

<img :src="$withBase('/images/application-restrictions.png')" class="dropshadow" alt="Screenshot of Google Application Restrictions for each API key" style="max-width:630px">

Remember, after creating the first key, you will need to repeat the process to create the second key!

:::tip Create Credentials
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials) _(login required)_
:::

## Adding your keys to Craft

Once you have created both API keys, you will need to add them to your Craft site. There are a few different ways to approach this, so go with what works for your setup. The preferred method is to set your keys in the project's `.env` file.

You can find the API key fields on the **Settings > Smart Map** page in your control panel.

<img :src="$withBase('/images/api-fields.png')" class="dropshadow" alt="Screenshot of Google API keys settings" style="max-width:630px; margin-bottom:16px;">

### Setting keys in fields directly

:::warning Discouraged - Don't do this
Storing your keys directly in the Settings fields is discouraged. It is considered a minor security risk, because your unsecured keys will end up in the database, as well as in the project config files (and therefore your Git repo).
:::

This is the least preferred method of storing your API keys. Saving them directly in the Settings fields is the least secure solution, and prevents you from using different API keys in different environments.

### Setting keys in `.env`

:::warning Encouraged - Do this
Using an `.env` file is the most secure and most flexible way to store your API keys.
:::

**This is the preferred approach.** Presumptively, you already have your database and other system details stored in an `.env` file. Simply add your keys to that file, and the Settings page will then automatically be able to recognize them.

```sh
# Google Maps API keys
GOOGLEMAPS_BROWSERKEY="YOUR_BROWSER_KEY"
GOOGLEMAPS_SERVERKEY="YOUR_SERVER_KEY"
```

Once that is in place in your `.env` file, it will then be possible for the Settings page to recognize those values.

Craft will store a **reference** to the keys, instead of storing the keys themselves. This allows you to keep your unsecured keys out of the database, and out of the project config files. It also gives you the flexibility to use different API keys in different environments.
