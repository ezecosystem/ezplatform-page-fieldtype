<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformPageFieldTypeBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use EzSystems\EzPlatformPageFieldTypeBundle\DependencyInjection\Configuration\Parser;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EzPublishCoreExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->extension = new EzPublishCoreExtension(
            [
                new Parser\Page(),
                new Parser\BlockView(),
            ]
        );
        $this->extension->addDefaultSettings(
            __DIR__ . '/../../../bundle/Resources/config',
            ['default_settings.yml']
        );
        $this->extension->addConfigurationRegistrar(
            function (array $config, ContainerBuilder $container)
            {
                if (isset($config['ezpage']['layouts'])) {
                    $container->setParameter(
                        'ezpublish.ezpage.layouts',
                        $config['ezpage']['layouts'] + $container->getParameter('ezpublish.ezpage.layouts')
                    );
                }
                if (isset($config['ezpage']['blocks'])) {
                    $container->setParameter(
                        'ezpublish.ezpage.blocks',
                        $config['ezpage']['blocks'] + $container->getParameter('ezpublish.ezpage.blocks')
                    );
                }
                if (isset($config['ezpage']['enabledLayouts'])) {
                    $container->setParameter(
                        'ezpublish.ezpage.enabledLayouts',
                        $config['ezpage']['enabledLayouts'] + $container->getParameter('ezpublish.ezpage.enabledLayouts')
                    );
                }
                if (isset($config['ezpage']['enabledBlocks'])) {
                    $container->setParameter(
                        'ezpublish.ezpage.enabledBlocks',
                        $config['ezpage']['enabledBlocks'] + $container->getParameter('ezpublish.ezpage.enabledBlocks')
                    );
                }
            }
        );

        parent::setUp();
    }

    protected function getContainerExtensions()
    {
        return array($this->extension);
    }

    public function testEzPageConfiguration()
    {
        $customLayouts = array(
            'FoobarLayout' => array('name' => 'Foo layout', 'template' => 'foolayout.html.twig'),
        );
        $enabledLayouts = array('FoobarLayout', 'GlobalZoneLayout');
        $customBlocks = array(
            'FoobarBlock' => array('name' => 'Foo block'),
        );
        $enabledBlocks = array('FoobarBlock', 'DemoBlock');
        $this->load(
            array(
                'ezpage' => array(
                    'layouts' => $customLayouts,
                    'blocks' => $customBlocks,
                    'enabledLayouts' => $enabledLayouts,
                    'enabledBlocks' => $enabledBlocks,
                ),
                'siteaccess' => array(
                    'default_siteaccess' => 'ezdemo_site',
                    'list' => array('ezdemo_site', 'eng', 'fre', 'ezdemo_site_admin'),
                    'groups' => array(
                        'ezdemo_group' => array('ezdemo_site', 'eng', 'fre', 'ezdemo_site_admin'),
                        'ezdemo_frontend_group' => array('ezdemo_site', 'eng', 'fre'),
                    ),
                    'match' => array(
                        'URILElement' => 1,
                        'Map\URI' => array('the_front' => 'ezdemo_site', 'the_back' => 'ezdemo_site_admin'),
                    ),
                ),
                'system' => array(
                    'ezdemo_site' => array(),
                    'eng' => array(),
                    'fre' => array(),
                    'ezdemo_site_admin' => array(),
                ),
            )
        );

        $this->assertTrue($this->container->hasParameter('ezpublish.ezpage.layouts'));
        $layouts = $this->container->getParameter('ezpublish.ezpage.layouts');
        $this->assertArrayHasKey('FoobarLayout', $layouts);
        $this->assertSame($customLayouts['FoobarLayout'], $layouts['FoobarLayout']);
        $this->assertContainerBuilderHasParameter('ezpublish.ezpage.enabledLayouts', $enabledLayouts);

        $this->assertTrue($this->container->hasParameter('ezpublish.ezpage.blocks'));
        $blocks = $this->container->getParameter('ezpublish.ezpage.blocks');
        $this->assertArrayHasKey('FoobarBlock', $blocks);
        $this->assertSame($customBlocks['FoobarBlock'], $blocks['FoobarBlock']);
        $this->assertContainerBuilderHasParameter('ezpublish.ezpage.enabledBlocks', $enabledBlocks);
    }
}
