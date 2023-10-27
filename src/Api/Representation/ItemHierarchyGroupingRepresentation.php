<?php
namespace ItemHierarchy\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class ItemHierarchyGroupingRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return [
            'parentGroupingID' => $this->resource->getParentGroupingID(),
            'label' => $this->resource->getLabel(),
            'itemSet' => $this->resource->getItemSet(),
            'hierarchy' => $this->resource->getHierarchy(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:ItemHierarchyGrouping';
    }

    public function getParentGroupingID()
    {
        return $this->resource->getParentGroupingID();
    }

    public function getLabel()
    {
        return $this->resource->getLabel();
    }

    public function getItemSet()
    {
        return $this->resource->getItemSet();
    }

    public function getGrouping()
    {
        return $this->resource->getGrouping();
    }
}
