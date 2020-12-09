---
description: It's possible to override the Google API keys on the fly.
---

# Override Google API keys

There may be instances where you need to overwrite the Google API keys from a Twig template.

```twig
{# Override server key #}
{% do craft.smartMap.setServerKey('lorem') %}

{# Override browser key #}
{% do craft.smartMap.setBrowserKey('ipsum') %}
```
