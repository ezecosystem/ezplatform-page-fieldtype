<?php

/**
 * This file is part of the eZ Platform Page Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Page\PageStorage\Gateway;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\FieldType\Page\PageStorage\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\Parts\Item;
use PDO;
use RuntimeException;
use DateTime;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

class LegacyStorage extends Gateway
{
    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * Set database handler for this gateway.
     *
     * @param mixed $dbHandler
     *
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     */
    public function setConnection($dbHandler)
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if (!$dbHandler instanceof DatabaseHandler) {
            throw new RuntimeException('Invalid dbHandler passed');
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection.
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ($this->dbHandler === null) {
            throw new RuntimeException('Missing database connection.');
        }

        return $this->dbHandler;
    }

    /**
     * Returns valid items (that are to be displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getValidBlockItems(Block $block)
    {
        $dbHandler = $this->getConnection();
        $q = $dbHandler->createSelectQuery();
        $q
            ->select('object_id, ezm_pool.node_id, ezm_pool.priority, ts_publication, ts_visible, rotation_until, moved_to')
            ->from($dbHandler->quoteTable('ezm_pool'))
            ->innerJoin(
                $dbHandler->quoteTable('ezcontentobject_tree'),
                $q->expr->eq('ezcontentobject_tree.node_id', 'ezm_pool.node_id')
            )
            ->where(
                $q->expr->eq('block_id', $q->bindValue($block->id)),
                $q->expr->gt('ts_visible', $q->bindValue(0, null, PDO::PARAM_INT)),
                $q->expr->eq('ts_hidden', $q->bindValue(0, null, PDO::PARAM_INT))
            )
            ->orderBy('priority', SelectQuery::DESC);

        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = array();
        foreach ($rows as $row) {
            $items[] = $this->buildBlockItem(
                $row + array(
                    'block_id' => $block->id,
                    'ts_hidden' => 0,
                )
            );
        }

        return $items;
    }

    /**
     * Returns the block item having a highest visible date, for given block.
     * Will return null if no block item is registered for block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item|null
     */
    public function getLastValidBlockItem(Block $block)
    {
        $dbHandler = $this->getConnection();
        $q = $dbHandler->createSelectQuery();
        $q
            ->select('object_id, node_id, priority, ts_publication, ts_visible, rotation_until, moved_to')
            ->from($dbHandler->quoteTable('ezm_pool'))
            ->where(
                $q->expr->eq('block_id', $q->bindValue($block->id)),
                $q->expr->gt('ts_visible', $q->bindValue(0, null, PDO::PARAM_INT)),
                $q->expr->eq('ts_hidden', $q->bindValue(0, null, PDO::PARAM_INT))
            )
            ->orderBy('ts_visible', SelectQuery::DESC)
            ->limit(1);

        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return;
        }

        return $this->buildBlockItem(
            $rows[0] + array(
                'block_id' => $block->id,
                'ts_hidden' => 0,
            )
        );
    }

    /**
     * Returns queued items (the next to be displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getWaitingBlockItems(Block $block)
    {
        $dbHandler = $this->getConnection();
        $q = $dbHandler->createSelectQuery();
        $q
            ->select('object_id, node_id, priority, ts_publication, rotation_until, moved_to')
            ->from($dbHandler->quoteTable('ezm_pool'))
            ->where(
                $q->expr->eq('block_id', $q->bindValue($block->id)),
                $q->expr->eq('ts_visible', $q->bindValue(0, null, PDO::PARAM_INT)),
                $q->expr->eq('ts_hidden', $q->bindValue(0, null, PDO::PARAM_INT))
            )
            ->orderBy('ts_publication')
            ->orderBy('priority');

        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = array();
        foreach ($rows as $row) {
            $items[] = $this->buildBlockItem(
                $row + array(
                    'block_id' => $block->id,
                    'ts_visible' => 0,
                    'ts_hidden' => 0,
                )
            );
        }

        return $items;
    }

    /**
     * Returns archived items (that were previously displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getArchivedBlockItems(Block $block)
    {
        $dbHandler = $this->getConnection();
        $q = $dbHandler->createSelectQuery();
        $q
            ->select('object_id, node_id, priority, ts_publication, ts_visible, ts_hidden, rotation_until, moved_to')
            ->from($dbHandler->quoteTable('ezm_pool'))
            ->where(
                $q->expr->eq('block_id', $q->bindValue($block->id)),
                $q->expr->gt('ts_hidden', $q->bindValue(0, null, PDO::PARAM_INT))
            )
            ->orderBy('ts_hidden');

        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = array();
        foreach ($rows as $row) {
            $items[] = $this->buildBlockItem(
                $row + array(
                    'block_id' => $block->id,
                )
            );
        }

        return $items;
    }

    /**
     * Returns Content id for the given Block $id,
     * or false if Block could not be found.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If block could not be found.
     *
     * @param int|string $id
     *
     * @return int
     */
    public function getContentIdByBlockId($id)
    {
        $dbHandler = $this->getConnection();
        $query = $dbHandler->createSelectQuery();
        $query
            ->select($dbHandler->quoteColumn('contentobject_id'))
            ->from($dbHandler->quoteTable('ezcontentobject_tree'))
            ->innerJoin(
                $dbHandler->quoteTable('ezm_block'),
                $query->expr->eq(
                    $dbHandler->quoteColumn('node_id', 'ezm_block'),
                    $dbHandler->quoteColumn('node_id', 'ezcontentobject_tree')
                )
            )
            ->where(
                $query->expr->eq(
                    $dbHandler->quoteColumn('id', 'ezm_block'),
                    $query->bindValue($id, null, PDO::PARAM_STR)
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        $contentId = $stmt->fetchColumn();

        if ($contentId === false) {
            throw new NotFoundException('Block', $id);
        }

        return $contentId;
    }

    /**
     * Builds a Page\Parts\Item object from a row returned from ezm_pool table.
     *
     * @param array $row Hash representing a block item as stored in ezm_pool table.
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item
     */
    protected function buildBlockItem(array $row)
    {
        return new Item(
            array(
                'blockId' => $row['block_id'],
                'contentId' => (int)$row['object_id'],
                'locationId' => (int)$row['node_id'],
                'priority' => (int)$row['priority'],
                'publicationDate' => new DateTime("@{$row['ts_publication']}"),
                'visibilityDate' => $row['ts_visible'] ? new DateTime("@{$row['ts_visible']}") : null,
                'hiddenDate' => $row['ts_hidden'] ? new DateTime("@{$row['ts_hidden']}") : null,
                'rotationUntilDate' => $row['rotation_until'] ? new DateTime("@{$row['rotation_until']}") : null,
                'movedTo' => $row['moved_to'],
            )
        );
    }
}
