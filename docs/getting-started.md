---
price: 49
pluginName: Smart Map
tagline: The most comprehensive proximity search and mapping tool for Craft.
description: The most comprehensive proximity search and mapping tool for Craft.
pluginStore: https://plugins.craftcms.com/smart-map
---

# Getting Started

There are two ways to install {{ $frontmatter.pluginName }}...

## Install using Composer

<p>Open your terminal and run the following commands:</p>

```shell
# Go to the project directory
cd /path/to/project

# Tell Composer to load the plugin
composer require doublesecretagency/craft-smartmap

# Tell Craft to install the plugin
./craft install/plugin smart-map
```

## Install from the Plugin Store

Within your website's Plugin Store, search for **{{ $frontmatter.pluginName }}**.

1. Login to your website's Control Panel.
2. Go to the Plugin Store.
3. Search for **{{ $frontmatter.pluginName }}**.
4. Click the one that looks like this:

<div style="
    display: flex;
    padding: 20px 23px 2px;
    border: 1px solid #e3e5e8;
    border-radius: 5px;
    box-sizing: border-box;
    position: relative;
    width: 360px;
    margin: 0 10px;
    font-size: 14px; margin-bottom:16px
">
    <div style="margin-right:20px">
        <img :src="$withBase('/images/icon.svg')" width="100" alt="">
    </div>
    <div>
        <strong style="font-size:17px">{{ $frontmatter.pluginName }}</strong>
        <div style="font-size:15px; margin-top:9px;">{{ $frontmatter.tagline }}</div>
        <p style="color:#8f98a3 !important; font-weight:normal;">${{ $frontmatter.price }}</p>
    </div>
</div>

Then follow the instructions on that page to install the plugin. **Scroll down** to "Try" or "Add to Cart".

:::warning Public Page in the Plugin Store
For reference, check out the <a :href="$frontmatter.pluginStore" target="_blank">equivalent page</a> in the public plugin store.
:::
