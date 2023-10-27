<?php
namespace ItemHierarchy\Api\Adapter;

use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class ItemHierarchyGroupingAdapter extends AbstractEntityAdapter
{
    public function getEntityClass()
    {
        return 'ItemHierarchy\Entity\ItemHierarchyGrouping';
    }

    public function getResourceName()
    {
        return 'item_hierarchy_grouping';
    }

    public function getRepresentationClass()
    {
        return 'ItemHierarchy\Api\Representation\ItemHierarchyGroupingRepresentation';
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $data = $request->getContent();
        if (isset($data['o:item_set']['o:id'])) {
            $itemSet = $this->getAdapter('item_sets')->findEntity($data['o:item_set']['o:id']);
            $entity->setItemSet($itemSet);
        }
        if (isset($data['parentGroupingID'])) {
            $entity->setParentGroupingID($data['parentGroupingID']);
        }
        if (isset($data['label'])) {
            $entity->setLabel($data['label']);
        }
        if (isset($data['hierarchy'])) {
            $entity->setBlock($data['hierarchy']);
        }
    }
}
