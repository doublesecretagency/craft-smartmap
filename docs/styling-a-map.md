---
description: It's common to create a set of map styles in JSON, and very easy to apply those JS styles to your map.
---

# Styling a Map

<update-message/>

It's common for people to use a site like [Snazzy Maps](https://snazzymaps.com) to generate a JSON styling for their map. Once you've got a JSON snippet from there, you'll want to apply those styles to your map in JavaScript.

```js
var styles = [...];
smartMap.styleMap('smartmap-mapcanvas-1', styles);
```

You'll want to delay that JS code until the page has fully loaded.

```js
// Delay with jQuery
$(function () {
    var styles = [...];
    smartMap.styleMap('smartmap-mapcanvas-1', styles);
});
```

You may also be trying to load this through a Twig template (possibly the same Twig template that's loading your map). In that case, simply wrap your JS with a `js` tag pair.

```twig
{# Load via Twig #}
{% js %}

    // Delay with jQuery
    $(function () {
        var styles = [...];
        smartMap.styleMap('smartmap-mapcanvas-1', styles);
    });

{% endjs %}
```
