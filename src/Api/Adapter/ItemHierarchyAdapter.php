<?php
namespace ItemHierarchy\Api\Adapter;

use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class ItemHierarchyAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'position' => 'position',
    ];

    public function getEntityClass()
    {
        return 'ItemHierarchy\Entity\ItemHierarchy';
    }

    public function getResourceName()
    {
        return 'item_hierarchy';
    }

    public function getRepresentationClass()
    {
        return 'ItemHierarchy\Api\Representation\ItemHierarchyRepresentation';
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $data = $request->getContent();
        if (isset($data['label'])) {
            $entity->setLabel($data['label']);
        }
        if (isset($data['data'])) {
            $entity->setData($data['data']);
        }
        // (Re-)order blocks by their order in the input
        static $position = 1;
        if (isset($data['position'])) {
            $entity->setPosition($position++);
        }
    }
}
