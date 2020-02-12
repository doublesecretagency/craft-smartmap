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
use doublesecretagency\smartmap\enums\MeasurementUnit;
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
        'street1' => ['enable' => 1, 'width' => 100, 'position' => 1],
        'street2' => ['enable' => 1, 'width' => 100, 'position' => 2],
        'city'    => ['enable' => 1, 'width' =>  50, 'position' => 3],
        'state'   => ['enable' => 1, 'width' =>  15, 'position' => 4],
        'zip'     => ['enable' => 1, 'width' =>  35, 'position' => 5],
        'country' => ['enable' => 1, 'width' => 100, 'position' => 6],
        'lat'     => ['enable' => 1, 'width' =>  50, 'position' => 7],
        'lng'     => ['enable' => 1, 'width' =>  50, 'position' => 8],
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

        // Get settings
        $settings = $this->getSettings();

        // Restructure layout
        $layout = $this->_extractLayout($settings);
        unset($settings['layout']);

        // Render fieldtype settings template
        return $view->renderTemplate('smart-map/address/fieldtype-settings', [
            'settings' => $settings,
            'layout' => $layout,
            'containerId' => 'id-'.StringHelper::randomString(10),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Reference assets
        $view = Craft::$app->getView();
        $view->registerAssetBundle(GoogleMapsAssets::class);
        $view->registerAssetBundle(FieldInputAssets::class);

        // Ensure geo data is loaded
        SmartMap::$plugin->smartMap->measurementUnit = MeasurementUnit::Miles;
        SmartMap::$plugin->smartMap->loadGeoData();

        // Get visitor coordinates
        $visitor = SmartMap::$plugin->smartMap->visitor;
        if ($visitor['latitude'] && $visitor['longitude']) {
            $visitorJs = json_encode([
                'lat' => $visitor['latitude'],
                'lng' => $visitor['longitude'],
            ]);
        } else {
            $visitorJs = 'false';
        }

        // Register visitor JS
        $view->registerJs('visitor = '.$visitorJs.';', $view::POS_END);

        // Extract layout
        $settings = $this->getSettings();
        $layout = $this->_extractLayout($settings);

        // Load template
        return $view->renderTemplate('smart-map/address/input', [
            'name' => $this->handle,
            'value' => $value,
            'field' => $this,
            'layout' => $layout,
        ]);
    }

    /**
     * Extract layout data from settings
     *
     * @param $settings
     * @return array
     */
    private function _extractLayout($settings)
    {
        // Initialize
        $i = 0;
        $layout = [];

        // Loop through layout data
        foreach ($settings['layout'] as $handle => $subfield) {
            $i++;
            $position = ($subfield['position'] ?? $i);
            $layout[$position] = array_merge($subfield, ['handle' => $handle]);
        }

        // Cleanup
        ksort($layout);

        return $layout;
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
