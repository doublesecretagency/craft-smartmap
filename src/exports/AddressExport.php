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

namespace doublesecretagency\smartmap\exports;

use Craft;
use craft\base\Element;
use craft\base\ElementExporter;
use craft\base\Field;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use doublesecretagency\smartmap\fields\Address;

/**
 * Class AddressExport
 * @since 3.3.0
 */
class AddressExport extends ElementExporter
{
    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        $Address  = Craft::t('smart-map', 'Address');
        $SmartMap = Craft::t('smart-map', 'Smart Map');
        return "{$Address} ({$SmartMap})";
    }

    /**
     * @inheritDoc
     */
    public function export(ElementQueryInterface $query): array
    {
        // Initialize
        $results = [];

        // Determine which are the address fields
        $addressFields = [];
        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            /** @var Field $field */
            if ($field instanceof Address) {
                $addressFields[] = $field;
            }
        }

        /** @var Element $element */
        /** @var ElementQuery $query */

        // Loop through each element
        foreach ($query->each() as $element) {

            // Initialize with basic element data
            $data = [
                [
                    Craft::t('app', 'ID')    => $element->id    ?? '',
                    Craft::t('app', 'Title') => $element->title ?? '',
                ]
            ];

            // Append each address field to data
            foreach ($addressFields as $field) {
                $prefix = "{$field->name} - ";
                $address = $element->getFieldValue($field->handle);
                $data[] = [
                    $prefix.Craft::t('smart-map', 'Street Address')     => $address->street1,
                    $prefix.Craft::t('smart-map', 'Apartment or Suite') => $address->street2,
                    $prefix.Craft::t('smart-map', 'City')               => $address->city,
                    $prefix.Craft::t('smart-map', 'State')              => $address->state,
                    $prefix.Craft::t('smart-map', 'Zip Code')           => $address->zip,
                    $prefix.Craft::t('smart-map', 'Country')            => $address->country,
                    $prefix.Craft::t('smart-map', 'Latitude')           => $address->lat,
                    $prefix.Craft::t('smart-map', 'Longitude')          => $address->lng,
                ];
            }

            // Add new row to results
            $results[] = array_merge(...$data);

        }

        // Return results
        return $results;
    }

}
