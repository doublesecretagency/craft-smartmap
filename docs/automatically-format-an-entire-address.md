---
description: You can easily display a complete, nicely formatted address.
---

# Automatically format an entire address

You can keep the entire address on a single line by calling the field directly as a string...

```twig
{{ entry.myAddressField }}

123 Main St, Suite #101, Springfield, CO 81073
```

Alternately, you can use the `format` method to split the address up into multiple lines...

```twig
{{ entry.myAddressField.format }}

123 Main St<br>
Suite #101<br>
Springfield, CO 81073
```

## Optional line breaks

There may be occasions where you don't want the address to occupy three full lines. The `format` method provides two optional parameters:

 - Keep **apartment or suite** info on same line? (Defaults to `false`)
 - Keep **city & state** info on same line? (Defaults to `false`)

So to keep your unit information on the first line, simply add a `true` parameter...

```twig
{{ entry.myAddressField.format(true) }}

{# Puts the unit number on the first line #}

123 Main St, Suite #101<br>
Springfield, CO 81073
```

Or you can even flatten it all to a single line by adding another `true` parameter...

```twig
{{ entry.myAddressField.format(true, true) }}

{# Displays the entire address on a single line #}

123 Main St, Suite #101, Springfield, CO 81073
```
