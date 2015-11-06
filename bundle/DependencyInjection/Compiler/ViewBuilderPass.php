<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformPageFieldTypeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Block view builder with view builder registry.
 */
class ViewBuilderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $registryServiceId = 'ezpublish.view_builder.registry';
        $builderServiceId = 'ezpublish.view_builder.block';

        if (
            !$container->hasDefinition($registryServiceId) ||
            !$container->hasDefinition($builderServiceId)
        ) {
            return;
        }

        $registryDefinition = $container->getDefinition($registryServiceId);
        $registryDefinition->addMethodCall(
            'addToRegistry',
            [[new Reference($builderServiceId)]]
        );
    }
}
