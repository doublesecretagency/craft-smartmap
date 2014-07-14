<?php
namespace Craft;

class SmartMap_AddressFieldType extends BaseFieldType
{
    public function getName()
    {
        return Craft::t('Address (Smart Map)');
    }
    // ==================================================== //

    // Modify SQL query
    public function modifyElementsQuery(DbCommand $query, $params)
    {
        if ($params !== null) {
            craft()->smartMap->modifyQuery($query, $params);
        }
    }

    // Render HTML for field input
    public function getInputHtml($name, $value) // $value has been prepared by prepValue()
    {

        craft()->smartMap->measurementUnit = MeasurementUnit::Miles;

        $api  = '//maps.googleapis.com/maps/api/js';
        $api .= craft()->smartMap->appendGoogleApiKey('?');

        craft()->templates->includeJsFile($api);
        craft()->templates->includeJsResource('smartmap/js/address-fieldtype.js');
        craft()->templates->includeCssResource('smartmap/css/address-fieldtype.css');

        $here = craft()->smartMap->here;
        if ($here['latitude'] && $here['longitude']) {
            $hereJs = json_encode(array(
                'lat' => $here['latitude'],
                'lng' => $here['longitude'],
            ));
        } else {
            $hereJs = 'false';
        }
        craft()->templates->includeJs('var here = '.$hereJs.';');

        if (!empty($value)) {
            $addressModel = SmartMap_AddressModel::populateModel($value);
        } else {
            $addressModel = new SmartMap_AddressModel;
            $addressModel->handle = $name;
        }

        return craft()->templates->render('smartmap/address/input', $addressModel->getAttributes());
        
    }

    // Don't put field value into "craft_content" table
    public function defineContentAttribute()
    {
        return false;
    }

    /* Currently not using any custom settings
    public function getSettingsHtml()
    {
        return craft()->templates->render('smartmap/address/settings', array(
            //'settings' => $this->getSettings()
        ));
    }

    protected function defineSettings()
    {
        return array(
            'initialSlots' => array(AttributeType::Number, 'min' => 0)
        );
    }
    */

    // ==================================================== //

    /*
    // As the data enters the database
    public function prepValueFromPost($value)
    {
        // Called before onAfterSave() 
        return $value;
    }
    */

    // As the data leaves the database
    public function prepValue($value)
    {
        // Ignoring $value on purpose (it's the empty value from the _content table)
        return craft()->smartMap->getAddress($this);
    }

    // ==================================================== //
    // EVENTS
    // ==================================================== //

    // 
    //public function onBeforeSave() {}

    // 
    //public function onAfterSave() {}

    // After saving element, save field to plugin table
    public function onAfterElementSave()
    {
        // Returns true if entry was saved
        return craft()->smartMap->saveAddressField($this);
    }

}