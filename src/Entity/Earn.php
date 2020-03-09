<?php

namespace App\Entity;

use App\Entity\Interfaces\EarnInterface;
use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="earns",
 *     indexes={
 *     @ORM\Index(name="earns_place_description_idx", columns={"place_id", "description"}),
 *     @ORM\Index(name="earns_place_type", columns={"place_id", "type"}),
 *     @ORM\Index(name="earns_place_earn_at", columns={"place_id", "earn_at"}),
 *     @ORM\Index(name="earns_place_confirmed_at", columns={"place_id", "confirmed_at"}),
 *     @ORM\Index(name="earns_place_place", columns={"place_id", "place_id"})
 * })
 */
class Earn extends ModelBase implements EarnInterface, ModelInterface, SimpleTimeInterface, \JsonSerializable
{
    use SimpleTime;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $description;

    /**
     * @Assert\Date(
     *     groups="Y-m-d",
     *     message="The Earn At must be a date (Y-m-d)!")
     * @ORM\Column(type="date")
     */
    protected $earn_at;

    /**
     * @Assert\NotBlank(message="A Value is required!")
     * @Assert\Positive(message="Value must be a decimal equal or greater than zero!")
     * @Assert\Type(
     *     type="Numeric",
     *     message="Value must be a decimal equal or greater than zero!")
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    protected $value;

    /**
     * @Assert\Date(
     *     groups="Y-m-d H:i:s",
     *     message="The Confirmed At must be a datetime!")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $confirmed_at;

    /**
     * @Assert\NotBlank(message="The type is required!")
     * @Assert\Choice(choices=GeneralTypes::TYPE_EARN_LIST)
     * @ORM\Column(type="string", length=30)
     */
    protected $type;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @var Place
     * @Assert\NotBlank(message="A Place is required!")
     * @ORM\ManyToOne(targetEntity="Place", inversedBy="earns")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $place;

    protected $attributes = [
        "description",
        "type",
        "place",
        "earn_at",
        "value"
    ];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->type = GeneralTypes::TYPE_MOONLIGHTING;
        $this->earn_at = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPlaceId(): ?string
    {
        if (empty($this->place)) {
            return "";
        }

        return $this->place->getId();
    }

    public function getPlaceIDAndDescription(): ?array
    {
        if (empty($this->place)) {
            return [];
        }

        return $this->place->getIdAndDescription();
    }

    public function getOriginalData(): array
    {
        return [
            "id" => $this->id,
            "description" => $this->description,
            "value" => $this->value,
            "confirmed_at" => $this->confirmed_at,
            "type" => $this->type,
            "created_at" => $this->created_at,
            "earn_at" => $this->earn_at,
            "place_id" => $this->getPlaceId(),
            "updated_at" => $this->updated_at
        ];
    }

    public function getFullData(): array
    {
        return [
            "id" => $this->id,
            "description" => $this->description,
            "value" => $this->value,
            "confirmed_at" => $this->getDateTimeStringFrom('confirmed_at'),
            "type" => $this->type,
            "type_description" => GeneralTypes::getTypeEarnDescription($this->type),
            "place" => $this->getPlaceIDAndDescription(),
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "earn_at" => $this->getDateTimeStringFrom('earn_at', 'Y-m-d'),
            "updated_at" => $this->getDateTimeStringFrom('updated_at'),
        ];
    }

    public function getAllAttributesDateAndFormat(): array
    {
        return [
            "earn_at" => [
                "format" => "Y-m-d",
                "message" => "Earn At is invalid, should be in format Y-m-d"
            ],
            "confirmed_at" => [
                "format" => "Y-m-d H:i:s",
                "message" => "Confirmed at is invalid, should be in format Y-m-d H:i:s"
            ]
        ];
    }

    public function jsonSerialize()
    {
        return $this->getFullData();
    }

    public function setPlace(Place $place)
    {
        $this->place = $place;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function earnConfirmed()
    {
        $this->confirmed_at = new \DateTime();
    }
}
