<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package App\Entity
 */
class ModelBase
{
    protected $attributes = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deleted_at;

    public function setAttributes(array $values): void
    {
        if (empty($values) ||
            !$this->attributes ||
            count($this->attributes) < 1) {
            return ;
        }

        foreach ($this->attributes as $attribute)
        {
            if (!array_key_exists($attribute, $values)) {
                continue;
            }

            if (!property_exists($this, $attribute)) {
                continue;
            }

            $this->setAttribute($attribute, $values[$attribute]);
        }
    }

    public function setAttribute(string $key, $value): void
    {
        $this->$key = $value;
    }

    /**
     * @throws \Exception
     */
    public function updateLastUpdated(): void
    {
        $this->updated_at = new \DateTime('now');
    }

    public function remove(): void
    {
        $this->deleted_at = new \DateTime('now');
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deleted_at;
    }
}