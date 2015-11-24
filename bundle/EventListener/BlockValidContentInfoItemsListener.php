<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformPageFieldTypeBundle\EventListener;

use eZ\Publish\Core\FieldType\Page\PageService;
use EzSystems\EzPlatformPageFieldTypeBundle\FieldType\Page\PageService as PageBundlePageService;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects valid ContentInfo items into the block view.
 */
class BlockValidContentInfoItemsListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\FieldType\Page\PageService|\EzSystems\EzPlatformPageFieldTypeBundle\FieldType\Page\PageService
     */
    protected $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectValidContentInfoItems'];
    }

    public function injectValidContentInfoItems(FilterViewParametersEvent $event)
    {
        $view = $event->getView();
        if ($view instanceof BlockView && $this->pageService instanceof PageBundlePageService) {
            $event->getParameterBag()->set(
                'valid_contentinfo_items',
                $this->pageService->getValidBlockItemsAsContentInfo($view->getBlock())
            );
        }
    }
}
