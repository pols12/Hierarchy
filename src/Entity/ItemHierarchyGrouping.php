<?php
namespace ItemHierarchy\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\ItemSet;
use ItemHierarchy\Entity\ItemHierarchyBlock;

/**
 * @Entity
 */
class ItemHierarchyGrouping extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $parent_grouping;

    /**
     * @Column(type="string")
     */
    protected $label;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\ItemSet")
     * @JoinColumn(nullable=false, onDelete="CASCADE")
     * @var int
     */
    protected $item_set;

    /**
     * @OneToOne(targetEntity="ItemHierarchy\Entity\ItemHierarchy")
     * @JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $hierarchy;

    public function getId()
    {
        return $this->id;
    }

    public function setParentGrouping($parentGrouping)
    {
        $this->parent_grouping = $parentGrouping;
    }

    public function getParentGroupingId()
    {
        return $this->parent_grouping_id;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }
    
    public function setItemSet(ItemSet $itemSet)
    {
        $this->item_set = $itemSet;
    }

    public function getItemSet()
    {
        return $this->item_set;
    }

    public function setHierarchy(ItemHierarchy $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    public function getHierarchy()
    {
        return $this->hierarchy;
    }
}
