<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class SmartMap_AddressFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'smartmap/_integrations/feedme/fields/smartmap_address';
    }

    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        // Initialize content array
        $content = array();

        $data = Hash::get($fieldData, 'data');

        foreach ($data as $subfieldHandle => $subfieldData) {
            // Set value to subfield of correct address array
            $content[$subfieldHandle] = Hash::get($subfieldData, 'data');
        }

        // Return data
        return $content;
    }

}