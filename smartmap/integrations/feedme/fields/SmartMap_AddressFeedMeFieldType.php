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

        // Normalise array indexes due to multitude of different ways we can be supplied data
        $attributes = array(
            'street1',
            'street2',
            'city',
            'state',
            'zip',
            'country',
            'lat',
            'lng',
        );

        foreach (Hash::flatten($data) as $key => $value) {
            foreach ($attributes as $attribute) {
                if (strstr($key, $attribute)) {
                    $newKey = $attribute;
                    break;
                }
            }

            if ($newKey) {
                $content[$newKey] = $value;
            }
        }

        // Return data
        return $content;
    }

}