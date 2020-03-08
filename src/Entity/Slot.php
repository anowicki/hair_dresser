<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get", "post"},
 *     itemOperations={"get", "put", "delete"},
 *     normalizationContext={"groups"={"slot:read"}, "swagger_definition_name"="Read"},
 *     denormalizationContext={"groups"={"slot:write"}, "swagger_definition_name"="Write"},
 *     attributes={
 *          "pagination_items_per_page"=168
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\SlotRepository")
 * @ApiFilter(DateFilter::class, properties={"reservationDate", })
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"stand", "reservationDate"})
 */
class Slot
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"slot:read", "slot:write", "user:read"})
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = 1,
     *      max = 2
     * )
     */
    private $stand;

    /**
     * The reservation slot.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"slot:read", "slot:write", "user:read"})
     * @Assert\NotBlank()
     * @Assert\DateTime()
     */
    private $reservationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"slot:read"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"slot:read"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="slots")
     * @Groups({"slot:read", "slot:write"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * Gets triggered only on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->setCreatedAt(new \DateTime('now'));
    }

    /**
     * Gets triggered every time on update
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->setUpdatedAt(new \DateTime('now'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStand(): ?int
    {
        return $this->stand;
    }

    public function setStand(int $stand): self
    {
        $this->stand = $stand;

        return $this;
    }

    public function getReservationDate(): ?\DateTimeInterface
    {
        return $this->reservationDate;
    }

    public function setReservationDate(\DateTimeInterface $reservationDate): self
    {
        if (!$this->checkDate($reservationDate)) {
            throw new BadRequestHttpException('Wrong date/time');
        }
        $this->reservationDate = $reservationDate->setTime ( $reservationDate->format("H"), $reservationDate->format("i"), 0 );

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function checkDate($reservationDate)
    {

        $fix_time =
            in_array($reservationDate->format('i'), array('00', '30')) &&
            in_array($reservationDate->format('H'), range(8,20));

        $date = \DateTime::createFromFormat('H:i', $reservationDate->format('H:i'));
        $open = \DateTime::createFromFormat('H:i', "08:00");
        $close = \DateTime::createFromFormat('H:i', "20:00");

        $time_between =
            $date >= $open && $date <= $close;

        return $fix_time && $time_between;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
