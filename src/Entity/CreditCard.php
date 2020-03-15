<?php

namespace App\Entity;

use App\Entity\Interfaces\CreditCardInterface;
use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\ReadUserOutsideInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\Traits\ControlStatusAndIsDefault;
use App\Entity\Traits\ReadUserData;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="credit_cards",
 *     indexes={
 *     @ORM\Index(name="credit_cards_status_type", columns={"status"})
 * })
 */
class CreditCard extends ModelBase implements CreditCardInterface, SimpleTimeInterface, ModelInterface, ReadUserOutsideInterface, \JsonSerializable
{
    use SimpleTime, ReadUserData, ControlStatusAndIsDefault;

    const FIRST_DAY_AT_MONTH = 1;
    const DEFAULT_DESCRIPTION = "My card";

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="Last Digits is required!")
     * @Assert\Range(
     *      min = 0000,
     *      max = 9999
     * )
     * @Assert\Type(
     *     type="integer",
     *     message="Last Digits must be an integer!"
     * )
     * @ORM\Column(type="integer")
     */
    protected $last_digits;

    /**
     * @Assert\NotBlank(message="Card Banner is required!")
     * @Assert\Choice(choices=GeneralTypes::BANNER_DEFAULT_LIST, message="Card Banner type is invalid!.")
     * @ORM\Column(type="string", length=20)
     */
    protected $card_banner;

    /**
     * @Assert\Range(
     *      min = 1,
     *      max = 30
     * )
     * @Assert\Type(
     *     type="integer",
     *     message="The Due Date must be an day of month!"
     * )
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    protected $due_date;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $is_default;

    /**
     * @Assert\Positive(message="Limit Value must be a decimal equal or greater than zero!")
     * @Assert\Type(
     *     type="Numeric",
     *     message="limit Value must be a decimal equal or greater than zero!")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    protected $limit_value;

    /**
     * @Assert\Date(
     *     groups="Y-m-d",
     *     message="The Validity must be filled with Year-Month!")
     * @ORM\Column(type="date", nullable=true)
     */
    protected $validity;

    /**
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $description;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="creditCards" )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\OneToMany(targetEntity="Expense", mappedBy="creditCard", orphanRemoval=true)
     */
    protected $expenses;

    /**
     * @Assert\NotBlank(message="The status is required!")
     * @Assert\Choice({"enable", "disable", "blocked"})
     * @ORM\Column(type="string", length=20, options={"default": "enable"})
     */
    protected $status;

    protected $attributes = [
        "description",
        "user",
        "limit_value",
        "due_date",
        "validity",
        "card_banner",
        "last_digits"
    ];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->status = GeneralTypes::STATUS_ENABLE;
        $this->is_default = GeneralTypes::DEFAULT_UNSET;
        $this->due_date = self::FIRST_DAY_AT_MONTH;
        $this->expenses = new ArrayCollection();
        $this->description = self::DEFAULT_DESCRIPTION;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDetailsToExpense(): array
    {
        return [
            "id" => $this->id,
            "description" => $this->description,
            "card_banner" => $this->card_banner,
            "due_date" => $this->due_date
        ];
    }

    public function getOriginalData(): array
    {
        return [
            "id" => $this->id,
            "status" => $this->status,
            "description" => $this->description,
            "last_digits" => $this->last_digits,
            "due_date" => $this->due_date,
            "card_banner" => $this->card_banner,
            "is_default" => (bool)$this->is_default,
            "limit_value" => $this->limit_value,
            "validity" => $this->validity,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at,
            "user_id" => $this->getIdUser($this->user)
        ];
    }

    public function getFullData(): array
    {
        return [
            "id" => $this->id,
            "status" => $this->status,
            "status_description"  => GeneralTypes::getDefaultDescription($this->status),
            "description" => $this->description,
            "last_digits" => $this->last_digits,
            "due_date" => $this->due_date,
            "card_banner" => $this->card_banner,
            "is_default" => (bool)$this->is_default,
            "is_default_description"=>GeneralTypes::getDefaultSettingDescription($this->is_default),
            "limit_value" => $this->limit_value,
            "validity" => $this->getDateTimeStringFrom('validity', 'Y-m-d'),
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "updated_at" => $this->getDateTimeStringFrom('updated_at'),
            "deleted_at" => $this->getDateTimeStringFrom('deleted_at'),
            "user_id" => $this->getIdUser($this->user)
        ];
    }

    public function getAllAttributesDateAndFormat(): array
    {
        return [
            "validity" => [
                "format" => "Y-m-d",
                "message" => "Validity is invalid, should be in format Y-m-d"
            ]
        ];
    }

    public function jsonSerialize()
    {
        return $this->getFullData();
    }

    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses[] = $expense;
            $expense->setCreditCard($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses->removeElement($expense);
            // set the owning side to null (unless already changed)
            if ($expense->getCreditCard() === $this) {
                $expense->setCreditCard(null);
            }
        }

        return $this;
    }
}
