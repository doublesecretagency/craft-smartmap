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

        craft()->smartMap->mapApi = MapApi::GoogleMaps;
        craft()->smartMap->measurementUnit = MeasurementUnit::Miles;

        switch (craft()->smartMap->mapApi) {
            case MapApi::GoogleMaps:
            default:
                $apiJs = '//maps.googleapis.com/maps/api/js?key='.craft()->smartMap->mapApiKey.'&sensor=false';
                break;
        }

        craft()->templates->includeJsFile($apiJs);
        craft()->templates->includeJsResource('smartmap/js/fieldtype.js');
        craft()->templates->includeCssResource('smartmap/css/fieldtype.css');

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