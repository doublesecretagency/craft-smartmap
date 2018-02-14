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

/**
 * Class DebugAssets
 * @since 3.0.0
 */
class DebugAssets extends AssetBundle
{

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $this->sourcePath = '@doublesecretagency/smartmap/resources';

        $this->css = [
            'css/debug.css',
        ];

        $this->js = [
            'js/debug.js',
        ];
    }

}