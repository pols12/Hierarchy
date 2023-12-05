<?php
namespace ItemHierarchy\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
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

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['item_set'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.item_set',
                $this->createNamedParameter($qb, $query['item_set']))
            );
        }
        if (isset($query['parent_grouping'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.parent_grouping',
                $this->createNamedParameter($qb, $query['parent_grouping']))
            );
        }
        if (isset($query['label'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.label',
                $this->createNamedParameter($qb, $query['label']))
            );
        }
        if (isset($query['hierarchy'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.hierarchy',
                $this->createNamedParameter($qb, $query['hierarchy']))
            );
        }
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $data = $request->getContent();
        if (isset($data['item_set'])) {
            $itemSet = $this->getAdapter('item_sets')->findEntity($data['item_set']);
            $entity->setItemSet($itemSet);
        }
        if (isset($data['parent_grouping'])) {
            $entity->setParentGrouping($data['parent_grouping']);
        }
        if (isset($data['label'])) {
            $entity->setLabel($data['label']);
        }
        if (isset($data['hierarchy'])) {
            $hierarchy = $this->getAdapter('item_hierarchy')->findEntity($data['hierarchy']);
            $entity->setHierarchy($hierarchy);
        }
    }
}
