<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\Core\FieldType\Page\Parts\Block;

interface BlockValueView
{
    /**
     * @return Block
     */
    public function getBlock();
}
