<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatusRepository::class)
 */
class Status
{
    const NEW         = 'new';
    const IN_PROGRESS = 'in_progress';
    const CLOSED      = 'closed';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $redmineId = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $redmineName = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type = '';

    /**
     * @ORM\Column(type="boolean")
     */
    private $forReview = false;

    /**
     * @return mixed
     */
    public function getRedmineId()
    {
        return $this->redmineId;
    }

    /**
     * @param mixed $redmineId
     */
    public function setRedmineId($redmineId): void
    {
        $this->redmineId = $redmineId;
    }

    /**
     * @return mixed
     */
    public function getRedmineName()
    {
        return $this->redmineName;
    }

    /**
     * @param mixed $redmineName
     */
    public function setRedmineName($redmineName): void
    {
        $this->redmineName = $redmineName;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getForReview(): ?bool
    {
        return $this->forReview;
    }

    public function setForReview(bool $forReview): self
    {
        $this->forReview = $forReview;

        return $this;
    }
}
