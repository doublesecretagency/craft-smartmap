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

namespace doublesecretagency\smartmap\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\StringHelper;

use doublesecretagency\smartmap\SmartMap;
use doublesecretagency\smartmap\web\assets\FieldInputAssets;
use doublesecretagency\smartmap\web\assets\FieldSettingsAssets;
use doublesecretagency\smartmap\web\assets\GoogleMapsAssets;

/**
 * Class Address
 * @since 3.0.0
 */
class Address extends Field implements PreviewableFieldInterface
{

    /**
     * @var array|null
     */
    public $layout = [
        'street1' => ['enable' => 1, 'width' => 100],
        'street2' => ['enable' => 1, 'width' => 100],
        'city'    => ['enable' => 1, 'width' =>  50],
        'state'   => ['enable' => 1, 'width' =>  15],
        'zip'     => ['enable' => 1, 'width' =>  35],
        'country' => ['enable' => 1, 'width' => 100],
        'lat'     => ['enable' => 1, 'width' =>  50],
        'lng'     => ['enable' => 1, 'width' =>  50],
    ];

    /**
     * @var bool|null
     */
    public $dragPinDefault;

    /**
     * @var float|null
     */
    public $dragPinLatitude;

    /**
     * @var float|null
     */
    public $dragPinLongitude;

    /**
     * @var int|null
     */
    public $dragPinZoom;

    // ========================================================================= //

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        $Address  = Craft::t('smart-map', 'Address');
        $SmartMap = Craft::t('smart-map', 'Smart Map');
        return "{$Address} ({$SmartMap})";
    }

    /**
     * @inheritdoc
     */
    public static function hasContentColumn(): bool
    {
        return false;
    }

    // ========================================================================= //

    /**
     * After saving element, save field to plugin table.
     *
     * @inheritdoc
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        SmartMap::$plugin->smartMap->saveAddressField($this, $element);
    }

    /**
     * Prep value for use as the data leaves the database.
     *
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return SmartMap::$plugin->smartMap->getAddressField($this, $element, $value);
    }

    // ========================================================================= //

    /**
     * @inheritdoc
     */
    public function getSettings(): array
    {
        $settings = parent::getSettings();

        // Ensure layout is an array
        if (!is_array($settings['layout'])) {
            $settings['layout'] = json_decode($settings['layout'], true);
        }

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): string
    {
        // Reference assets
        $view = Craft::$app->getView();
        $view->registerAssetBundle(GoogleMapsAssets::class);
        $view->registerAssetBundle(FieldSettingsAssets::class);

        // Render fieldtype settings template
        return $view->renderTemplate('smart-map/address/fieldtype-settings', [
            'settings' => $this->getSettings(),
            'containerId' => 'id-'.StringHelper::randomString(10),
        ]);
    }

    // ========================================================================= //

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Reference assets
        $view = Craft::$app->getView();
        $view->registerAssetBundle(GoogleMapsAssets::class);
        $view->registerAssetBundle(FieldInputAssets::class);

        // TODO: Clean up this method


//        SmartMap::$plugin->smartMap->measurementUnit = MeasurementUnit::Miles;

//        SmartMap::$plugin->smartMap->loadGeoData();

//        $visitor = SmartMap::$plugin->smartMap->visitor;
//        if ($visitor['latitude'] && $visitor['longitude']) {
//            $visitorJs = json_encode([
//                'lat' => $visitor['latitude'],
//                'lng' => $visitor['longitude'],
//            ]);
//        } else {
//            $visitorJs = 'false';
//        }
//        $view->registerJs('visitor = '.$visitorJs.';', $view::POS_END);


        return $view->renderTemplate('smart-map/address/input', [
            'name' => $this->handle,
            'value' => $value,
            'field' => $this,
        ]);
    }

    // ========================================================================= //

    /**
     * @inheritdoc
     */
    public function modifyElementsQuery(ElementQueryInterface $query, $params)
    {
        // If no params, bail
        if (!$params) {
            return null;
        }
        // If params are not an array, bail
        if (!is_array($params)) {
            return null;
        }
        // Modify the query
        $params['fieldId']     = $this->id;
        $params['fieldHandle'] = $this->handle;
        SmartMap::$plugin->smartMap->modifyQuery($query, $params);
    }

    // ========================================================================= //

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return [
            ['validateCoords'],
        ];
    }

    public function validateCoords(ElementInterface $element)
    {
        $address = $element->getFieldValue($this->handle);

        $hasLat = (bool) $address->lat;
        $hasLng = (bool) $address->lng;
        $validLat = ($hasLat ? is_numeric($address->lat) : true);
        $validLng = ($hasLng ? is_numeric($address->lng) : true);

        if (!($validLat && $validLng)) {
            $element->addError($this->handle, Craft::t('smart-map', 'If coordinates are specified, they must be numbers.'));
        }
    }

}