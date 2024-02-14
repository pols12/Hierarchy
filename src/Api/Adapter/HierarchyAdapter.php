<?php
namespace Hierarchy\Api\Adapter;

use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class HierarchyAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'position' => 'position',
    ];

    public function getEntityClass()
    {
        return 'Hierarchy\Entity\Hierarchy';
    }

    public function getResourceName()
    {
        return 'hierarchy';
    }

    public function getRepresentationClass()
    {
        return 'Hierarchy\Api\Representation\HierarchyRepresentation';
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $data = $request->getContent();
        if (isset($data['label'])) {
            $entity->setLabel($data['label']);
        }
        // (Re-)order blocks by their order in the input
        static $position = 1;
        if (isset($data['position'])) {
            $entity->setPosition($position++);
        }
    }

    public function validateEntity(EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $label = $entity->getLabel();
        if (false == trim($label)) {
            $errorStore->addError('o:label', 'The hierarchy label cannot be empty.'); // @translate
        }
        if (!$this->isUnique($entity, ['label' => $label])) {
            $errorStore->addError('label', 'The hierarchy label is already taken.'); // @translate
        }
    }
}
