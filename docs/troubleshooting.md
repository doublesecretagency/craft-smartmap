---
description: Here are a few common issues that people face when getting started...
---

# Troubleshooting

<update-message/>

## The map isn't appearing at all

Check your DOM to see if the map container has been generated. If it has, then you're probably facing a simple CSS issue. By default, the `height` of a div will be zero... So even though the container is there, it isn't visible.

### Ways to fix it:

 - Add a `height` value to your map options.
 - Set the height of `.smartmap-mapcanvas` in your CSS.

## It says "Loading map..." but the map never loads

Looks like something is wrong with your JavaScript. Open your browser's console to try to locate the problem.

If nothing is showing up in the browser's console, you may be using [Craft's cache tag](https://craftcms.com/docs/3.x/dev/tags.html#cache) in Twig. There is a known issue with Craft in which plugins are unable to have their JavaScript loaded from cache.

### Way to fix it:

 - Omit your Smart Map tags from your caching. End the caching tag before the Smart Map tag, then restart the cache afterwards. This will allow the plugin to properly trigger its JavaScript every time.

## I can see the map, but it isn't fully loading

It's possible that you are simply missing Google API keys.

 - Learn how to [get Google API keys...](/get-google-api-keys/)

## I'm loading the map inside a hidden element

When the map gets initially loaded inside a hidden element, it may need to be refreshed when that element becomes visible. Fortunately, it's very easy to trigger a map refresh in JavaScript:

```js
smartMap.refreshMap('smartmap-mapcanvas-1');
```

The above code assumes that your map ID is set to the default `smartmap-mapcanvas-1`. If not, simply replace that string with your _actual_ map ID.
