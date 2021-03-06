<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Page;

use eZ\Publish\Core\FieldType\Page\Parts\Page;
use eZ\Publish\Core\FieldType\Page\Parts\Zone;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\Parts\Item;

/**
 * Class HashConverter converts between a Page field type Value object and a representation
 * of the same in a plain hash format.
 */
class HashConverter
{
    /**
     * Converts the given $value into a plain hash format.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Value $value
     *
     * @return array
     */
    public function convertFromValue(Value $value)
    {
        $hash = array();

        foreach ($value->page->getState() as $propName => $propValue) {
            switch ($propName) {
                case 'layout':
                    if ($propValue !== null) {
                        $hash['layout'] = $propValue;
                    }
                    break;
                case 'attributes':
                    if ($propValue !== null && $propValue !== array()) {
                        $hash['attributes'] = $propValue;
                    }
                    break;
                case 'zones':
                    foreach ($propValue as $zone) {
                        $hash['zones'][] = $this->convertZoneToHash($zone);
                    }
                    break;
            }
        }

        return $hash;
    }

    /**
     * Converts the given $zone into a plain hash format.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Zone $zone
     *
     * @return array
     */
    protected function convertZoneToHash(Zone $zone)
    {
        $hash = array();

        foreach ($zone->getState() as $propName => $propValue) {
            switch ($propName) {
                case 'id':
                case 'identifier':
                case 'action':
                    if ($propValue !== null) {
                        $hash[$propName] = $propValue;
                    }
                    break;
                case 'attributes':
                    if ($propValue !== null && $propValue !== array()) {
                        $hash['attributes'] = $propValue;
                    }
                    break;
                case 'blocks':
                    foreach ($propValue as $block) {
                        $hash['blocks'][] = $this->convertBlockToHash($block);
                    }
                    break;
            }
        }

        return $hash;
    }

    /**
     * Converts the given $block into a plain hash format.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return array
     */
    protected function convertBlockToHash(Block $block)
    {
        $hash = array();

        foreach ($block->getState() as $propName => $propValue) {
            switch ($propName) {
                case 'id':
                case 'name':
                case 'type':
                case 'view':
                case 'overflowId':
                case 'customAttributes':
                case 'action':
                case 'rotation':
                case 'zoneId':
                    if ($propValue !== null) {
                        $hash[$propName] = $propValue;
                    }
                    break;
                case 'attributes':
                    if ($propValue !== null && $propValue !== array()) {
                        $hash['attributes'] = $propValue;
                    }
                    break;
                case 'items':
                    foreach ($propValue as $item) {
                        $hash['items'][] = $this->convertItemToHash($item);
                    }
                    break;
            }
        }

        return $hash;
    }

    /**
     * Converts the given $item into a plain hash format.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Item $item
     *
     * @return array
     */
    protected function convertItemToHash(Item $item)
    {
        $hash = array();

        foreach ($item->getState() as $propName => $propValue) {
            switch ($propName) {
                case 'blockId':
                case 'contentId':
                case 'locationId':
                case 'priority':
                case 'movedTo':
                case 'action':
                    if ($propValue !== null) {
                        $hash[$propName] = $propValue;
                    }
                    break;
                case 'attributes':
                    if ($propValue !== null && $propValue !== array()) {
                        $hash['attributes'] = $propValue;
                    }
                    break;
                case 'publicationDate':
                case 'visibilityDate':
                case 'hiddenDate':
                case 'rotationUntilDate':
                    if ($propValue !== null) {
                        /* @var $propValue \DateTime */
                        $hash[$propName] = $propValue->format(\DateTime::RFC850);
                    }
                    break;
            }
        }

        return $hash;
    }

    /**
     * Converts the given $hash to a Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Page\Value
     */
    public function convertToValue($hash)
    {
        if (isset($hash['zones'])) {
            $zones = array();

            foreach ($hash['zones'] as $zone) {
                $zones[] = $this->convertZoneFromHash($zone);
            }

            $hash['zones'] = $zones;
        }

        return new Value(new Page($hash));
    }

    /**
     * Converts the given $hash to a Zone node.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Zone
     */
    protected function convertZoneFromHash($hash)
    {
        if (isset($hash['blocks'])) {
            $blocks = array();

            foreach ($hash['blocks'] as $block) {
                $blocks[] = $this->convertBlockFromHash($block);
            }

            $hash['blocks'] = $blocks;
        }

        return new Zone($hash);
    }

    /**
     * Converts the given $hash to a Block node.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    protected function convertBlockFromHash($hash)
    {
        if (isset($hash['items'])) {
            $items = array();

            foreach ($hash['items'] as $item) {
                $items[] = $this->convertItemFromHash($item);
            }

            $hash['items'] = $items;
        }

        return new Block($hash);
    }

    /**
     * Converts the given $hash to a Item node.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item
     */
    protected function convertItemFromHash($hash)
    {
        foreach ($hash as $key => &$item) {
            switch ($key) {
                case 'publicationDate':
                case 'visibilityDate':
                case 'hiddenDate':
                case 'rotationUntilDate':
                    // $item is expected to be a date string in RFC850 format
                    $item = new \DateTime($item);
                    break;
            }
        }

        return new Item($hash);
    }
}
