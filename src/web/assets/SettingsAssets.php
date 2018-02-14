<?php
/**
 * Smart Map plugin for Craft CMS
 *
 * The most comprehensive proximity search and mapping tool for Craft.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2014 Double Secret Agency
 */

namespace doublesecretagency\smartmap\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class SettingsAssets
 * @since 3.0.0
 */
class SettingsAssets extends AssetBundle
{

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $this->sourcePath = '@doublesecretagency/smartmap/resources';
        $this->depends = [CpAsset::class];

        $this->css = [
            'css/settings.css',
        ];

        $this->js = [
            'js/settings.js',
        ];
    }

}