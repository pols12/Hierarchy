<?php
namespace ItemHierarchy\Entity;

use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 */
class ItemHierarchy extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(unique=true)
     */
    protected $label;

    /**
     * @Column(type="integer")
     */
    protected $position;

    public function getId()
    {
        return $this->id;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }
}
