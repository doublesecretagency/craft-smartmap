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
 * @since 3.2.2
 */
class m190310_000000_smartMap_addPositionToFieldLayouts extends Migration
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
     * @throws \Throwable
     */
    public function safeUp()
    {
        // Get fields service
        $fieldsService = Craft::$app->getFields();

        // Get all Address fields
        $allFields = (new Query())
            ->select(['id'])
            ->from(['{{%fields}}'])
            ->where(['type' => Address::class])
            ->column();

        // Get all fields
        // REQUIRES CRAFT 3.1
//        $allFields = $fieldsService->getAllFields(false);

        // Loop through all fields
//        foreach ($allFields as $field) {
        foreach ($allFields as $fieldId) {

            /** @var FieldInterface $field */
            $field = $fieldsService->getFieldById($fieldId);

            // If not an Address field, skip
            // REQUIRES CRAFT 3.1
//            if (Address::class !== get_class($field)) {
//                continue;
//            }

            // Get field settings
            $settings = $field->getSettings();

            // If layout doesn't exist or is misconfigured
            if (!array_key_exists('layout', $settings) || !$settings['layout']) {

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
            if (!array_key_exists('width', $subfield) || !array_key_exists('enable', $subfield)) {

                // Use the default layout
                $field->layout = $this->_defaultLayout;
                $fieldsService->saveField($field, false);

                // Skip to next field
                continue;
            }

            // If positions are already set, skip to next field
            if (array_key_exists('position', $subfield)) {
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
        echo "m190310_000000_smartMap_addPositionToFieldLayouts cannot be reverted.\n";

        return false;
    }

}
