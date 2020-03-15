<?php

namespace App\Entity;

use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\PlaceInterface;
use App\Entity\Interfaces\ReadUserOutsideInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\Traits\ReadUserData;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="places",
 *      uniqueConstraints={@ORM\UniqueConstraint(columns={"user_id", "description"})},
 *     indexes={
 *     @ORM\Index(name="places_status_type_idx", columns={"status"})
 * })
 */
class Place extends ModelBase implements PlaceInterface, ModelInterface, SimpleTimeInterface, \JsonSerializable, ReadUserOutsideInterface
{
    use SimpleTime, ReadUserData;

    const DESCRIPTION_DEFAULT = "home";
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="A user is required!")
     * @ORM\ManyToOne(targetEntity="User", inversedBy="places")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @Assert\NotBlank(message="A description is required!")
     * @Assert\Length(
     *      max = 30,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=30, nullable=true, options={"default": "home"})
     */
    protected $description = self::DESCRIPTION_DEFAULT;

    /**
     * @Assert\NotBlank(message="A status is required!")
     * @Assert\Choice({"enable", "disable", "blocked"})
     * @ORM\Column(type="string", length=20, options={"default": "enable"})
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\OneToMany(targetEntity="Expense", mappedBy="place", orphanRemoval=true)
     */
    protected $expenses;

    /**
     * @ORM\OneToMany(targetEntity="Earn", mappedBy="place", orphanRemoval=true)
     */
    protected $earns;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $is_default;

    protected $paramsListAll = [
        "description",
        "created_at",
        "status"
    ];

    protected $attributes = [
        "description"
    ];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->is_default = false;
        $this->status = GeneralTypes::STATUS_ENABLE;
        $this->expenses = new ArrayCollection();
        $this->earns = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFullData(): array
    {
        $userData = $this->getUser();
        $user_id  = empty($userData) ? null : $userData->getId();

        return [
            "id" => $this->id,
            "user_id" => $user_id,
            "description" => $this->description,
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "updated_at" => $this->getDateTimeStringFrom('updated_at'),
            "deleted_at" => $this->getDateTimeStringFrom('deleted_at'),
            "status" => $this->status,
            "is_default" => (bool)$this->is_default,
            "is_default_description" => GeneralTypes::getDefaultSettingDescription($this->is_default),
            "status_description" => GeneralTypes::getDefaultDescription($this->status)
        ];
    }

    public function getOriginalData(): array
    {
        return [
            "id" => $this->id,
            "user_id" => $this->user,
            "description" => $this->description,
            "is_default"  => (bool)$this->is_default,
            "created_at" => $this->created_at ,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at,
            "status" => $this->status
        ];
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function setAttributes(array $values): void
    {
        if ($this->getId()) {
            $this->updateLastUpdated();
        }

        parent::setAttributes($values);
    }

    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses[] = $expense;
            $expense->setPlace($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses->removeElement($expense);
            // set the owning side to null (unless already changed)
            if ($expense->getPlace() === $this) {
                $expense->setPlace(null);
            }
        }

        return $this;
    }

    public function setDefault(): void
    {
        $this->is_default = GeneralTypes::DEFAULT_SET;
    }

    public function getIdAndDescription(): array
    {
        return [
            "id" => $this->id,
            "description" => $this->description
        ];
    }

    public function jsonSerialize()
    {
        return $this->getFullData();
    }

    public function getEarns(): Collection
    {
        return $this->earns;
    }

    public function addEarn(Earn $earn): self
    {
        if (!$this->earns->contains($earn)) {
            $this->earns[] = $earn;
            $earn->setPlace($this);
        }

        return $this;
    }

    public function removeEarn(Earn $earn): self
    {
        if ($this->earns->contains($earn)) {
            $this->earns->removeElement($earn);
            // set the owning side to null (unless already changed)
            if ($earn->getPlace() === $this) {
                $earn->setPlace(null);
            }
        }

        return $this;
    }
}
