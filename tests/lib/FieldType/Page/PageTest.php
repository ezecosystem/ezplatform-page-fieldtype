<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformPageFieldType\Tests\FieldType\Page;

use eZ\Publish\Core\FieldType\Page\Parts\Page;
use eZ\Publish\Core\FieldType\Page\Parts\Zone;
use PHPUnit_Framework_TestCase;

class PageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Page::__construct
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::getState
     */
    public function testGetState()
    {
        $zone1 = new Zone(array('id' => 'foo'));
        $zone2 = new Zone(array('id' => 'bar'));
        $zone3 = new Zone(array('id' => 'baz'));
        $properties = array(
            'layout' => 'my_layout',
            'zones' => array($zone1, $zone2, $zone3),
        );
        $page = new Page($properties);
        $this->assertEquals(
            $properties + array(
                'zonesById' => array(
                    'foo' => $zone1,
                    'bar' => $zone2,
                    'baz' => $zone3,
                ),
                'attributes' => array(),
            ),
            $page->getState()
        );
    }
}
