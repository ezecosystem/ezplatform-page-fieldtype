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

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ViewManagerPass;

/**
 * The BlockViewPass adds DIC compiler pass related to block view.
 * This includes adding BlockViewProvider implementations.
 *
 * @see \eZ\Publish\Core\MVC\Symfony\View\Manager
 *
 * @deprecated since 6.0
 */
class BlockViewPass extends ViewManagerPass
{
    const VIEW_PROVIDER_IDENTIFIER = 'ezpublish.block_view_provider';
    const ADD_VIEW_PROVIDER_METHOD = 'addBlockViewProvider';
    const VIEW_TYPE = 'eZ\Publish\Core\MVC\Symfony\View\BlockView';
}
