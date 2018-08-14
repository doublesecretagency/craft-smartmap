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
use craft\elements\Entry;

use doublesecretagency\smartmap\enums\MeasurementUnit;

/**
 * Class FilterCriteria
 * @since 3.0.0
 */
class FilterCriteria extends Model
{

    /** @var string|null  $target  Explanation. */
    public $target;

    /** @var array|null  $coords  Explanation. */
    public $coords = ['lat' => NULL, 'lng' => NULL];

    /** @var int|float|null  $range  Explanation. */
    public $range = 25;

    /** @var string|null  $units  Explanation. */
    public $units = MeasurementUnit::Miles;

    /** @var array|null  $filter  Explanation. */
    public $filter = [];

    /** @var string|null  $elementType  Explanation. */
    public $elementType = Entry::class;

    /** @var string|null  $sectionHandle  Explanation. */
    public $sectionHandle;

    /** @var string|null  $fieldHandle  Explanation. */
    public $fieldHandle;

    /** @var int|null  $fieldId  Explanation. */
    public $fieldId;

    /** @var int|null  $page  Explanation. */
    public $page;

    /** @var int|null  $limit  Explanation. */
    public $limit = 20;

    /** @var int|null  $offset  Explanation. */
    public $offset = 0;

    /** @var array|null  $components  Explanation. */
    public $components = [];

}