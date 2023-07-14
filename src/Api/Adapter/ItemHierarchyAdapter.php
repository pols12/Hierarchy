<?php
namespace ItemHierarchy\Api\Adapter;

use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class ItemHierarchyAdapter extends AbstractEntityAdapter
{
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
        if (isset($data['position'])) {
            $entity->setPosition($data['position']);
        }
    }
}
