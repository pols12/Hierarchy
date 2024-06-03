<?php
namespace Hierarchy\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class HierarchyGroupingRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return [
            'parentGroupingID' => $this->resource->getParentGroupingID(),
            'label' => $this->resource->getLabel(),
            'itemSet' => $this->resource->getItemSet(),
            'hierarchy' => $this->resource->getHierarchy(),
            'position' => $this->getPosition(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:HierarchyGrouping';
    }

    public function getParentGrouping()
    {
        return $this->resource->getParentGrouping();
    }

    public function getLabel()
    {
        return $this->resource->getLabel();
    }

    public function getItemSet()
    {
        return $this->getAdapter('item_sets')->getRepresentation($this->resource->getItemSet());
    }

    public function getHierarchy()
    {
        return $this->resource->getHierarchy();
    }

    public function getPosition()
    {
        return $this->resource->getPosition();
    }
}
