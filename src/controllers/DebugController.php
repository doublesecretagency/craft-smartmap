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

namespace doublesecretagency\smartmap\controllers;

use Craft;
use craft\web\Controller;
use craft\web\View;

use doublesecretagency\smartmap\SmartMap;
use doublesecretagency\smartmap\web\assets\DebugAssets;

/**
 * Class DebugController
 * @since 3.0.0
 */
class DebugController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = true;

    // Get location information for debugging
    public function actionIndex()
    {
        SmartMap::$plugin->smartMap->loadGeoData();
        $view = Craft::$app->getView();
        $view->registerAssetBundle(DebugAssets::class);
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $this->renderTemplate('smart-map/_debug', [
            'visitor' => SmartMap::$plugin->smartMap->visitor
        ]);
    }

}