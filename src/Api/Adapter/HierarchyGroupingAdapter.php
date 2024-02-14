<?php
namespace Hierarchy\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class HierarchyGroupingAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'position' => 'position',
    ];

    public function getEntityClass()
    {
        return 'Hierarchy\Entity\HierarchyGrouping';
    }

    public function getResourceName()
    {
        return 'hierarchy_grouping';
    }

    public function getRepresentationClass()
    {
        return 'Hierarchy\Api\Representation\HierarchyGroupingRepresentation';
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
        if (isset($query['position'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.position',
                $this->createNamedParameter($qb, $query['position']))
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
            $hierarchy = $this->getAdapter('hierarchy')->findEntity($data['hierarchy']);
            $entity->setHierarchy($hierarchy);
        }
        // (Re-)order groupings by their order in the input
        static $position = 1;
        if (isset($data['position'])) {
            $entity->setPosition($position++);
        }
    }
}
