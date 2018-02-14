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

/**
 * Class ModalController
 * @since 3.0.0
 */
class ModalController extends Controller
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = true;

    // Load modal template for address search results
    public function actionAddressSearch()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $address = Craft::$app->getRequest()->getBodyParam('address');
        if (!trim($address)) {
            return $this->asJson([
                'success' => false,
                'message' => Craft::t('smart-map', 'No address provided. Please enter a partial address to search for.')
            ]);
        }

        $response = SmartMap::$plugin->smartMap_main->addressSearch($address);
        if ($response['success']) {
            $view = Craft::$app->getView();
            $view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $template = $view->renderTemplate('smart-map/_modals/address-search-results', [
                'searchResults' => $response['results'],
            ]);
            unset($response['results']);
            $response['template'] = trim($template);
        }

        return $this->asJson($response);
    }

}