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

namespace doublesecretagency\smartmap\migrations;

use Craft;
use craft\base\FieldInterface;
use craft\db\Migration;
use craft\db\Query;
use doublesecretagency\smartmap\fields\Address;

/**
 * Migration: Add a `position` value to Address layout subfields
 * @since 3.2.3
 */
class m190410_000000_smartMap_addPositionToFieldLayouts extends Migration
{

    /**
     * @var array Default Address field layout.
     */
    private $_defaultLayout = [
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
     * @inheritdoc
     * @return bool|null
     * @throws \Throwable
     */
    public function safeUp()
    {
        // If admin changes are not allowed, bail
        if (!Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            return true;
        }

        // Get fields service
        $fieldsService = Craft::$app->getFields();

        // Get all fields
        $allFields = $fieldsService->getAllFields(false);

        // Loop through all fields
        foreach ($allFields as $field) {

            // If not an Address field, skip
            if (Address::class !== get_class($field)) {
                continue;
            }

            // Get field settings
            $settings = $field->getSettings();

            // If no layout
            if (!isset($settings['layout'])) {

                // Use the default layout
                $field->layout = $this->_defaultLayout;
                $fieldsService->saveField($field, false);

                // Skip to next field
                continue;
            }

            // Get existing field layout
            $layout = $settings['layout'];

            // Get first subfield value
            $subfield = reset($layout);

            // If subfield is misconfigured
            if (!isset($subfield['width']) || !isset($subfield['enable'])) {

                // Use the default layout
                $field->layout = $this->_defaultLayout;
                $fieldsService->saveField($field, false);

                // Skip to next field
                continue;
            }

            // If positions are already set, skip to next field
            if (isset($subfield['position'])) {
                continue;
            }

            // Add position to each subfield
            $i = 1;
            foreach($layout as $handle => $settings){
                $layout[$handle]['position'] = $i++;
            }

            // Save updated layout
            $field->layout = $layout;
            $fieldsService->saveField($field, false);

        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190410_000000_smartMap_addPositionToFieldLayouts cannot be reverted.\n";

        return false;
    }

}
