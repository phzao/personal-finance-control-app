<?php

namespace App\Entity;

use App\Entity\Interfaces\CategoryInterface;
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
 * @ORM\Table(name="categories",
 *      uniqueConstraints={@ORM\UniqueConstraint(columns={"user_id", "description"})},
 *     indexes={
 *     @ORM\Index(name="categories_status_type_idx", columns={"status"}),
 *     @ORM\Index(name="categories_user_description_idx", columns={"user_id", "description"}),
 *     @ORM\Index(name="categories_user_status_idx", columns={"user_id", "status"})
 * })
 */
class Category extends ModelBase implements CategoryInterface, ModelInterface, SimpleTimeInterface, \JsonSerializable, ReadUserOutsideInterface
{
    use SimpleTime, ReadUserData, ControlStatusAndIsDefault;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="Description is required!")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=40, options={"default": "general"})
     */
    protected $description = "general";

    /**
     * @Assert\NotBlank(message="The status is required!")
     * @Assert\Choice({"enable", "disable", "blocked"})
     * @ORM\Column(type="string", length=20, options={"default": "enable"})
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="categories" )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $is_default;

    /**
     * @ORM\OneToMany(targetEntity="Expense", mappedBy="category", orphanRemoval=true)
     */
    protected $expenses;

    protected $attributes = [
        "description",
        "user"
    ];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->expenses = new ArrayCollection();
        $this->status = GeneralTypes::STATUS_ENABLE;
        $this->is_default = GeneralTypes::DEFAULT_UNSET;
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

    public function getOriginalData(): array
    {
        return [
            "id" => $this->id,
            "description" => $this->description,
            "status"  => $this->status,
            "user_id" => $this->user,
            "is_default" => $this->is_default,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at
        ];
    }

    public function getFullData(): array
    {
        return [
            "id" => $this->id,
            "description" => $this->description,
            "status" => $this->status,
            "status_description"  => GeneralTypes::getDefaultDescription($this->status),
            "user_id" => $this->getIdUser($this->user),
            "is_default" => $this->is_default,
            "is_default_description"=>GeneralTypes::getDefaultSettingDescription($this->is_default),
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "updated_at" => $this->getDateTimeStringFrom('updated_at'),
            "deleted_at" => $this->getDateTimeStringFrom('deleted_at')
        ];
    }

    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses[] = $expense;
            $expense->setCategory($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if ($this->expenses->contains($expense)) {
            $this->expenses->removeElement($expense);
            // set the owning side to null (unless already changed)
            if ($expense->getCategory() === $this) {
                $expense->setCategory(null);
            }
        }

        return $this;
    }

    public function jsonSerialize()
    {
        return $this->getFullData();
    }

    public function getCategoryInfo(): array
    {
        return [
            "id" => $this->id,
            "description" => $this->description,
            "status"  => $this->status,
            "is_default" => $this->is_default
        ];
    }
}
