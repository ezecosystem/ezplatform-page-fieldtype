<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformPageFieldTypeBundle;

use EzSystems\EzPlatformPageFieldTypeBundle\DependencyInjection\Compiler;
use EzSystems\EzPlatformPageFieldTypeBundle\DependencyInjection\Configuration\Parser as ConfigParser;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EzSystemsEzPlatformPageFieldTypeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\BlockViewPass());
        $container->addCompilerPass(new Compiler\ViewBuilderPass());

        /**
         * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension $eZExtension
         */
        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addConfigParser(new ConfigParser\BlockView());
        $eZExtension->addConfigParser(new ConfigParser\Page());
        $eZExtension->addDefaultSettings(__DIR__ . '/Resources/config', ['default_settings.yml']);
        $eZExtension->addConfigurationRegistrar(
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
    }
}
