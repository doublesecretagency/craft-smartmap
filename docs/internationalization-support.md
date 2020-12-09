---
description: You can completely rearrange the Address subfields! Watch the animated GIF for a brief demonstration.
---

# Internationalization Support

## Reorganize the Address Subfields

By default, the Address field is arranged according to American address formatting.

However, you can **completely rearrange the Address subfields...**

<img :src="$withBase('/images/custom-address-field.gif')" class="dropshadow" alt="">

### Before

<img :src="$withBase('/images/before.png')" class="dropshadow" alt="">

### After

<img :src="$withBase('/images/after.png')" class="dropshadow" alt="">

## Any Language

The entire plugin is **fully translatable**. If your language isn't already included in the plugin, feel free to add your own!

You can use the following as a starter language file:

```
smartmap/translations/xx.php
```

## Street Name & Street Number

The Address field will automatically detect whether the street's name or number should come first, based on which country the address was found in.

<img :src="$withBase('/images/main-st.png')" class="dropshadow" alt="">
