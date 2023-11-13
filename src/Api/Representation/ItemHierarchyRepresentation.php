<?php
namespace ItemHierarchy\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use ItemHierarchy\Entity\ItemHierarchy;

class ItemHierarchyRepresentation extends AbstractEntityRepresentation
{
    /**
     * @var ItemHierarchy
     */
    protected $hierarchy;

    /**
     * Construct the hierarchy object.
     *
     * @param ItemHierarchy $hierarchy
     */
    public function __construct(ItemHierarchy $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    public function getJsonLd()
    {
        return [
            'id' => $this->id(),
            'label' => $this->getLabel(),
            'position' => $this->getPosition(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:ItemHierarchy';
    }

    public function id()
    {
        return $this->hierarchy->getId();
    }

    public function getLabel()
    {
        return $this->hierarchy->getLabel();
    }

    public function getPosition()
    {
        return $this->hierarchy->getPosition();
    }
}
