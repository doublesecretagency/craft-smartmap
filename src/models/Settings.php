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

namespace doublesecretagency\smartmap\models;

use craft\base\Model;

/**
 * Class Settings
 * @since 3.0.0
 */
class Settings extends Model
{

    /** @var string|null  $googleServerKey  Google API Server Key. */
    public $googleServerKey;

    /** @var string|null  $googleBrowserKey  Google API Browser Key. */
    public $googleBrowserKey;

    /** @var string|null  $geolocation  Currently selected geolocation service. */
    public $geolocation;

    /** @var string|null  $ipstackAccessKey  ipstack API Access Key. */
    public $ipstackAccessKey;

    /** @var string|null  $maxmindService  MaxMind Service. */
    public $maxmindService;

    /** @var string|null  $maxmindUserId  MaxMind User ID. */
    public $maxmindUserId;

    /** @var string|null  $maxmindLicenseKey  MaxMind License Key. */
    public $maxmindLicenseKey;

    /** @var string|null  $debugRoute  Route to debug page. */
    public $debugRoute = 'map/debug';

}
