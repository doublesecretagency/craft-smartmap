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

namespace doublesecretagency\smartmap\listeners;

use markhuot\CraftQL\Events\GetFieldSchema;

/**
 * Class GetAddressFieldSchema
 * @since 3.1.0
 */
class GetAddressFieldSchema
{

    /**
     * Handle the request for the schema
     *
     * @param GetFieldSchema $event
     */
    function handle(GetFieldSchema $event)
    {
        $event->handled = true;

        // Get address data
        $address = $event->schema->createObjectType('SmartMapAddress');
        $address->addStringField('street1');
        $address->addStringField('street2');
        $address->addStringField('city');
        $address->addStringField('state');
        $address->addStringField('zip');
        $address->addStringField('country');
        $address->addFloatField('lat');
        $address->addFloatField('lng');

        $event->schema->addField($event->sender)->type($address);
    }

}
