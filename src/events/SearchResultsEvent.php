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
 * Class SearchResultsEvent
 * @since 3.3.0
 */
class SearchResultsEvent extends Event
{

    /** @var array The search results. */
    public $results = [];

}
