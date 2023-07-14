<?php
namespace ItemHierarchy\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class ItemHierarchyRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return [
            'label' => $this->resource->getLabel(),
            'data' => $this->resource->getData(),
            'position' => $this->resource->getPosition(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:ItemHierarchy';
    }

    public function getLabel()
    {
        return $this->resource->getLabel();
    }

    public function getData()
    {
        return $this->resource->getData();
    }

    public function getPosition()
    {
        return $this->resource->getPosition();
    }
}
