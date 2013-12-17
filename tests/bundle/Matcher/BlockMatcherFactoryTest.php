<?php
/**
 * File containing the BlockMatcherFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Matcher;

use eZ\Bundle\EzPublishCoreBundle\Matcher\BlockMatcherFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockMatcherFactoryTest extends BaseMatcherFactoryTest
{
    public function testGetMatcherForLocation()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $resolverMock = $this->getResolverMock( $matcherServiceIdentifier );
        $container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $container
            ->expects( $this->atLeastOnce() )
            ->method( 'has' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( $matcherServiceIdentifier, true )
                    )
                )
            );
        $container
            ->expects( $this->atLeastOnce() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.api.repository', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' ) ),
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $resolverMock ),
                        array( $matcherServiceIdentifier, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\Block\\MatcherInterface' ) ),
                    )
                )
            );

        $matcherFactory = new BlockMatcherFactory( $container );
        $matcherFactory->match( $this->getBlockMock(), 'full' );
    }

    public function testSetSiteAccessNull()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $resolverMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $container
            ->expects( $this->atLeastOnce() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.api.repository', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' ) ),
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $resolverMock ),
                        array( $matcherServiceIdentifier, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\MatcherInterface' ) ),
                    )
                )
            );

        $resolverMock
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'block_view' )
            ->will(
                $this->returnValue(
                    array(
                        'full' => array(
                            'matchRule' => array(
                                'template' => 'my_template.html.twig',
                                'match' => array(
                                    $matcherServiceIdentifier => 'someValue'
                                )
                            )
                        )
                    )
                )
            );
        $matcherFactory = new BlockMatcherFactory( $container );
        $matcherFactory->setSiteAccess();
    }

    public function testSetSiteAccess()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $resolverMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $container
            ->expects( $this->atLeastOnce() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.api.repository', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' ) ),
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $resolverMock ),
                        array( $matcherServiceIdentifier, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\MatcherInterface' ) ),
                    )
                )
            );

        $siteAccessName = 'siteaccess_name';
        $updatedMatchConfig = array(
            'full' => array(
                'matchRule2' => array(
                    'template' => 'my_other_template.html.twig',
                    'match' => array(
                        'foo' => array( 'bar' )
                    )
                )
            )
        );
        $resolverMock
            ->expects( $this->atLeastOnce() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array(
                            'location_view', null, null,
                            array(
                                'full' => array(
                                    'matchRule' => array(
                                        'template' => 'my_template.html.twig',
                                        'match' => array(
                                            $matcherServiceIdentifier => 'someValue'
                                        )
                                    )
                                )
                            )
                        ),
                        array( 'block_view', 'ezsettings', $siteAccessName, $updatedMatchConfig ),
                    )
                )
            );
        $matcherFactory = new BlockMatcherFactory( $container );
        $matcherFactory->setSiteAccess( new SiteAccess( $siteAccessName ) );

        $refObj = new \ReflectionObject( $matcherFactory );
        $refProp = $refObj->getProperty( 'matchConfig' );
        $refProp->setAccessible( true );
        $this->assertSame( $updatedMatchConfig, $refProp->getValue( $matcherFactory ) );
    }
}