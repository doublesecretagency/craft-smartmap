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
 * Class FrontEndAssets
 * @since 3.0.0
 */
class FrontEndAssets extends AssetBundle
{

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $this->sourcePath = '@doublesecretagency/smartmap/resources';

        $this->js = [
            'js/smartmap.js',
        ];
    }

}