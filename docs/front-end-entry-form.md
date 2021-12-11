---
description: On the front-end, you can easily save data in a standard front-end entry form.
---

# Front-End Entry Form

<update-message/>

On the front-end, you can easily save data in a standard [front-end entry form](https://craftcms.com/knowledge-base/entry-form).

The address field would look something like this...

```twig
<label>Street Address</label>
<input type="text" name="fields[myAddressField][street1]" value="">

<label>Apartment or Suite</label>
<input type="text" name="fields[myAddressField][street2]" value="">

<label>City</label>
<input type="text" name="fields[myAddressField][city]" value="">

<label>State</label>
<input type="text" name="fields[myAddressField][state]" value="">

<label>Zip Code</label>
<input type="text" name="fields[myAddressField][zip]" value="">

<label>Country</label>
<input type="text" name="fields[myAddressField][country]" value="">

<label>Latitude</label>
<input type="text" name="fields[myAddressField][lat]" value="">

<label>Longitude</label>
<input type="text" name="fields[myAddressField][lng]" value="">
```

If you are populating any of these values dynamically, you can also use them as hidden fields.
