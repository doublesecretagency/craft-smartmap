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

use doublesecretagency\smartmap\SmartMap;

/**
 * Class LookupController
 * @since 3.0.0
 */
class LookupController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = true;

    // Lookup a target location, returning full JSON
    public function actionIndex()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $target = $request->getBodyParam('target');
        $components = $request->getBodyParam('components');
        $components = $this->_explodeComponents($components);
        $response = SmartMap::$plugin->smartMap->lookup($target, $components);
        return $this->asJson($response);
    }

    // Lookup a target location, returning only coordinates of first result
    public function actionCoords()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $target = $request->getBodyParam('target');
        $components = $request->getBodyParam('components');
        $components = $this->_explodeComponents($components);
        $response = SmartMap::$plugin->smartMap->lookupCoords($target, $components);
        return $this->asJson($response);
    }

    // Convert components string into array
    private function _explodeComponents($componentsString)
    {
        $componentsArray = [];
        if (!trim($componentsString)) {
            return $componentsArray;
        }
        $mergedComponents = explode('|', trim($componentsString));
        foreach ($mergedComponents as $keyValue) {
            $c = explode(':', $keyValue);
            $componentsArray[$c[0]] = $c[1];
        }
        return $componentsArray;
    }

}
