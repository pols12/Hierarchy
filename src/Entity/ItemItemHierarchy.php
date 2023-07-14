<?php
namespace ItemHierarchy\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Item;

/**
 * @Entity
 */
class ItemItemHierarchy extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\Item")
     * @JoinColumn(nullable=false, onDelete="CASCADE")
     * @var int
     */
    protected $item;

    /**
     * @ManyToOne(targetEntity="ItemHierarchy\Entity\ItemHierarchyGrouping")
     * @JoinColumn(nullable=false, onDelete="CASCADE")
     * @var int
     */
    protected $item_hierarchy_grouping;

    public function getId()
    {
        return $this->id;
    }

    public function setItem(Item $item)
    {
        $this->item = $item;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function setItemHierarchyGrouping($itemHierarchyGrouping)
    {
        $this->$item_hierarchy_grouping = $itemHierarchyGrouping;
    }

    public function getItemHierarchyGrouping()
    {
        return $this->item_hierarchy_grouping;
    }
}
