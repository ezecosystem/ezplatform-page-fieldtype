<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformPageFieldType\Tests\MVC\Symfony\FieldType\Page;

use eZ\Publish\Core\MVC\Symfony\FieldType\Page\ParameterProvider;
use PHPUnit_Framework_TestCase;

class ParameterProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\Page\ParameterProvider::getViewParameters
     */
    public function testGetViewParameters()
    {
        $pageService = $this
            ->getMockBuilder('eZ\\Publish\\Core\\FieldType\\Page\\PageService')
            ->disableOriginalConstructor()
            ->getMock();
        $field = $this->getMock('eZ\\Publish\\API\\Repository\\Values\\Content\\Field');
        $parameterProvider = new ParameterProvider($pageService);
        $this->assertSame(
            array('pageService' => $pageService),
            $parameterProvider->getViewParameters($field)
        );
    }
}
