<?php

namespace App\Entity;

use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\Interfaces\UsuarioInterface;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="users",
 *     indexes={
 *     @ORM\Index(name="users_email_status_type_idx", columns={"email", "status"}),
 * })
 */
class User extends ModelBase implements UsuarioInterface, ModelInterface, SimpleTimeInterface
{
    use SimpleTime;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="Email is required!")
     * @Assert\Email
     * @Assert\Length(
     *      min = 6,
     *      max = 180,
     *      minMessage = "Email must be at least {{ limit }} characters long",
     *      maxMessage = "Email cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @var string The hashed password
     * @Assert\NotBlank(message="The password is required!")
     * @Assert\Length(
     *      min = 6,
     *      max = 6,
     *      minMessage = "Password must be at least {{ limit }} characters long",
     *      maxMessage = "Password cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @Assert\NotBlank(message="The status is required!")
     * @Assert\Choice({"enable", "disable", "blocked"})
     * @ORM\Column(type="string", length=20, options={"default": "enable"})
     */
    protected $status;

    /**
     * @Assert\Length(
     *      max = 70,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=70, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    protected $attributes = [
        "email",
        "name",
        "password"
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ApiToken", mappedBy="user", orphanRemoval=true)
     */
    private $apiTokens;

    /**
     * @ORM\OneToMany(targetEntity="Place", mappedBy="user", orphanRemoval=true)
     */
    private $places;

    /**
     * @ORM\OneToMany(targetEntity="Expense", mappedBy="registered_by", orphanRemoval=true)
     */
    private $expenses_registered;

    /**
     * @ORM\OneToMany(targetEntity="Expense", mappedBy="paid_by", orphanRemoval=true)
     */
    private $expenses_paid;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deleted_at;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="user", orphanRemoval=true)
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="CreditCard", mappedBy="user", orphanRemoval=true)
     */
    private $creditCards;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->apiTokens = new ArrayCollection();
        $this->created_at = new \DateTime("now");
        $this->categories = new ArrayCollection();
        $this->places = new ArrayCollection();
        $this->creditCards = new ArrayCollection();
        $this->status = GeneralTypes::STATUS_ENABLE;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function encryptPassword(UserPasswordEncoderInterface $passwordEncoder)
    {
        $password = empty($this->password) ? "" : $this->password;
        $this->password = $passwordEncoder->encodePassword($this, $password);
    }

    public function getFullData(): array
    {
        return [
            "id" => $this->getId(),
            "email" => $this->email,
            "name" => $this->name,
            "status" => $this->status,
            "status_description" => GeneralTypes::getDefaultDescription($this->status),
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "updated_at" => $this->getDateTimeStringFrom('updated_at')
        ];
    }

    public function getOriginalData(): array
    {
        return [
            "id" => $this->id,
            "email" => $this->email,
            "name" => $this->name,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at
        ];
    }

    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): self
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens[] = $apiToken;
            $apiToken->setUser($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): self
    {
        if ($this->apiTokens->contains($apiToken)) {
            $this->apiTokens->removeElement($apiToken);
            // set the owning side to null (unless already changed)
            if ($apiToken->getUser() === $this) {
                $apiToken->setUser(null);
            }
        }

        return $this;
    }

    public function getLoginData(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email
        ];
    }

    public function setDisable()
    {
        $this->status = GeneralTypes::STATUS_DISABLE;
    }

    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function addPlace(Place $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places[] = $place;
            $place->setUser($this);
        }

        return $this;
    }

    public function removePlace(Place $place): self
    {
        if ($this->places->contains($place)) {
            $this->places->removeElement($place);
            // set the owning side to null (unless already changed)
            if ($place->getUser() === $this) {
                $place->setUser(null);
            }
        }

        return $this;
    }

    public function getCreditCards(): Collection
    {
        return $this->creditCards;
    }

    public function addCreditCard(CreditCard $creditCard): self
    {
        if (!$this->creditCards->contains($creditCard)) {
            $this->creditCards[] = $creditCard;
            $creditCard->setUser($this);
        }

        return $this;
    }

    public function removeCreditCard(CreditCard $creditCard): self
    {
        if ($this->creditCards->contains($creditCard)) {
            $this->creditCards->removeElement($creditCard);
            // set the owning side to null (unless already changed)
            if ($creditCard->getUser() === $this) {
                $creditCard->setUser(null);
            }
        }

        return $this;
    }

    public function getUser(): User
    {
        return $this;
    }

    public function getStatus(): string
    {
        return (string) $this->status;
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

    /**
     * @throws \Exception
     */
    public function delete(): void
    {
        $this->deleted_at = new \DateTime('now');
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setUser($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getUser() === $this) {
                $category->setUser(null);
            }
        }

        return $this;
    }

    public function getName(): string
    {
        return (string)$this->name;
    }

    public function getNameAndId(): array
    {
        $name = $this->name ?? "";

        return [
            "id" => $this->id,
            "name" => $name
        ];
    }

    public function jsonSerialize()
    {
        return $this->getFullData();
    }

    public function canAuthenticate(): bool
    {
        if ($this->status === GeneralTypes::STATUS_BLOCKED ||
            $this->status === GeneralTypes::STATUS_DISABLE ||
            empty($this->id) ||
            !empty($this->deleted_at)){
            return false;
        }

        return true;
    }

    public function changePasswordIfWantTo(array $requestData): array
    {
        if (!empty($requestData["new_password"]) &&
            !empty($requestData["password"])) {
            $requestData["password"] = $requestData["new_password"];
        }

        return $requestData;
    }

    public function removeEmailIfExist(array $requestData): array
    {
        if (!empty($requestData["email"])) {
            unset($requestData["email"]);
        }

        return $requestData;
    }
}
