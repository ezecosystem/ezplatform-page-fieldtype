<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Page\Parts;

/**
 * @property-read string $layout The layout identifier (e.g. "2ZonesLayout1").
 * @property-read \eZ\Publish\Core\FieldType\Page\Parts\Zone[] $zones Zone objects for current page, numerically indexed.
 * @property-read \eZ\Publish\Core\FieldType\Page\Parts\Zone[] $zonesById Zone objects for current page, indexed by their Id.
 */
class Page extends Base
{
    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Zone[]
     */
    protected $zones = array();

    /**
     * @var array
     */
    protected $zonesById = array();

    /**
     * @var string
     */
    protected $layout;

    public function __construct(array $properties = array())
    {
        parent::__construct($properties);

        foreach ($this->zones as $zone) {
            $this->zonesById[$zone->id] = $zone;
        }
    }
}
