<?php
namespace Hierarchy\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Hierarchy\Entity\Hierarchy;

class HierarchyRepresentation extends AbstractEntityRepresentation
{
    /**
     * @var Hierarchy
     */
    protected $hierarchy;

    /**
     * Construct the hierarchy object.
     *
     * @param Hierarchy $hierarchy
     */
    public function __construct(Hierarchy $hierarchy)
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
        return 'o:Hierarchy';
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
