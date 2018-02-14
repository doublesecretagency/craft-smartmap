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

namespace doublesecretagency\smartmap\events;

use yii\base\Event;

/**
 * Class DetectLocationEvent
 * @since 3.0.0
 */
class DetectLocationEvent extends Event
{

    /** @var string|null The visitor's IP address. */
    public $ip;

    /** @var array|null The visitor's location data. */
    public $location = [];

    /** @var string|null Which service was used to detect the location. */
    public $detectionService;

    /** @var string|null When the cache is due to expire. */
    public $cacheExpires;

}