<?php
namespace ItemHierarchy\Entity;

use Omeka\Entity\AbstractEntity;
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
    protected $parent_grouping_id;

    /**
     * @Column(unique=true)
     */
    protected $label;
    
    /**
     * @OneToOne(targetEntity="ItemHierarchy\Entity\ItemHierarchy")
     * @JoinColumn(nullable=false)
     */
    protected $hierarchy;

    public function getId()
    {
        return $this->id;
    }

    public function setParentGroupingId($parentGroupingId)
    {
        $this->parent_grouping_id = $parentGroupingId;
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
    
    public function setHierarchy(ItemHierarchy $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    public function getHierarchy()
    {
        return $this->hierarchy;
    }
}
