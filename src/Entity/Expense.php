<?php

namespace App\Entity;

use App\Entity\Interfaces\ExpenseInterface;
use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\ReadUserOutsideInterface;
use App\Entity\Interfaces\SearchDataInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\Traits\ParamControl;
use App\Entity\Traits\ReadUserData;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use App\Utils\Generators\TokenGeneratorInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="expenses",
 *     indexes={
 *     @ORM\Index(name="expenses_status_type_idx", columns={"status"}),
 *     @ORM\Index(name="expenses_status_paid_at_type_idx", columns={"status", "paid_at"}),
 *     @ORM\Index(name="expenses_value_status_type_idx", columns={"value", "status"}),
 *     @ORM\Index(name="expenses_payment_type_idx", columns={"payment_type"}),
 *     @ORM\Index(name="expenses_payment_type_paid_at_idx", columns={"payment_type", "paid_at"}),
 *     @ORM\Index(name="expenses_due_date_idx", columns={"due_date"}),
 *     @ORM\Index(name="expenses_created_at_idx", columns={"created_at"}),
 *     @ORM\Index(name="expenses_status_payment_type_idx", columns={"status", "payment_type"}),
 *     @ORM\Index(name="expenses_registered_by_description_idx", columns={"registered_by_id", "description"}),
 *     @ORM\Index(name="expenses_paid_by_description_idx", columns={"paid_by_id", "description"}),
 *     @ORM\Index(name="expenses_registered_by_status_idx", columns={"registered_by_id", "status"}),
 *     @ORM\Index(name="expenses_credit_card_id_due_date", columns={"credit_card_id", "due_date"}),
 *     @ORM\Index(name="expenses_credit_card_id_due_date_status", columns={"credit_card_id", "due_date", "status"}),
 *     @ORM\Index(name="expenses_credit_card_id_value", columns={"credit_card_id", "value"})
 * })
 */
class Expense extends ModelBase implements ModelInterface, ExpenseInterface, SimpleTimeInterface, SearchDataInterface, \JsonSerializable, ReadUserOutsideInterface
{
    use ParamControl, SimpleTime, ReadUserData;

    const FIRST_INSTALLMENT = 1;
    const MIN_INSTALLMENT = 1;
    const LENGTH_STRING_TOKEN = 125;
    const VALUE_MUST_BE_GREATER_THAN = 0;
    const ROUND_PRECISION = 2;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="A description is required!")
     * @Assert\Length(
     *      max = 70,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=70)
     */
    protected $description;

    /**
     * @Assert\Date(
     *     groups="Y-m-d",
     *     message="Due date must be a valid date!")
     * @ORM\Column(type="date", nullable=true)
     */
    protected $due_date;

    /**
     * @Assert\Date(
     *     groups="Y-m-d H:i:s",
     *     message="The Paid Date must be a valid datetime!")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $paid_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @Assert\Range(
     *      min = 1,
     *      max = 200,
     *     maxMessage="Total Installments must be smaller than 200!"
     * )
     * @Assert\Positive(message="Total of Installments must be an integer greater than zero!")
     * @Assert\Type(
     *     type="integer",
     *     message="Total of Installments must be an integer!"
     * )
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    protected $total_installments;

    /**
     * @Assert\Positive(message="Installment Number must be an integer greater than zero!")
     * @Assert\Type(
     *     type="integer",
     *     message="Installment Number must be an integer!"
     * )
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    protected $installment_number;

    /**
     * @Assert\NotBlank(message="A Status is required!")
     * @Assert\Choice({"pending", "paid", "overdue", "canceled"})
     * @ORM\Column(type="string", length=20, options={"default": "pending"})
     */
    protected $status;

    /**
     * @Assert\Choice(
     *     choices=GeneralTypes::STATUS_PAYMENT_LIST,
     *     message="Payment Type Invalid!"
     *     )
     * @ORM\Column(type="string", length=30)
     */
    protected $payment_type;

    /**
     * @Assert\Positive(message="Value must be a decimal equal or greater than zero!")
     * @Assert\Type(
     *     type="Numeric",
     *     message="Value must be a decimal equal or greater than zero!")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    protected $value;

    /**
     * @Assert\Positive(message="Amount Paid must be a decimal equal or greater than zero!")
     * @Assert\Type(
     *     type="Numeric",
     *     message="Amount paid must be a decimal equal or greater than zero!")
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    protected $amount_paid;

    /**
     * @var User
     * @Assert\NotBlank(message="It is necessary to inform the person responsible for the registration!")
     * @ORM\ManyToOne(targetEntity="User", inversedBy="expenses_registered")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $registered_by;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="expenses_paid")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    protected $paid_by;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $token_installment_group;

    /**
     * @var Place
     * @Assert\NotBlank(message="A Place is required!")
     * @ORM\ManyToOne(targetEntity="Place", inversedBy="expenses")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $place;

    /**
     * @var CreditCard
     * @ORM\ManyToOne(targetEntity="CreditCard", inversedBy="expenses")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $creditCard;

    /**
     * @var Category
     * @Assert\NotBlank(message="A Category is required!")
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="expenses")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $category;

    protected $fieldsMustNotChange = [
        "installment_number"
    ];

    protected $paramsListAll = [
        "description",
        "created_at",
        "status"
    ];

    protected $attributes = [
        "description",
        "registered_by",
        "paid_by",
        "place_id",
        "due_date",
        "paid_at",
        "total_installments",
        "installment_number",
        "payment_type",
        "value",
        "category",
        "place",
        "status",
        "creditCard",
        "amount_paid"
    ];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->status = GeneralTypes::STATUS_PENDING;
        $this->total_installments = self::MIN_INSTALLMENT;
        $this->installment_number = self::FIRST_INSTALLMENT;
        $this->payment_type = GeneralTypes::STATUS_PAYMENT_CASH;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): void
    {
        $this->place = $place;
    }

    public function getCreditCard(): ?CreditCard
    {
        return $this->creditCard;
    }

    public function setCreditCard(?CreditCard $creditCard): void
    {
        $this->creditCard = $creditCard;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): ? Category
    {
        return $this->category;
    }

    public function getOriginalData(): array
    {
        return [
            "id" => $this->id,
            "description"=>$this->description,
            "place" => $this->place,
            "registered_by" => $this->registered_by,
            "category" => $this->category,
            "value" => $this->value,
            "amount_paid"  => $this->amount_paid,
            "payment_type" => $this->payment_type,
            "status" => $this->status,
            "installment_number" => $this->installment_number,
            "total_installments" => $this->total_installments,
            "paid_by" => $this->paid_by,
            "paid_at" => $this->paid_at,
            "due_date" => $this->due_date,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "token_installment_group" => $this->token_installment_group
        ];
    }

    public function getPlaceIdDescription(): array
    {
        if (!$this->place instanceof Place) {
            return [];
        }

        return $this->place->getIdAndDescription();
    }

    public function getPlaceId(): string
    {
        if (!$this->place instanceof Place) {
            return '';
        }

        return $this->place->getId();
    }

    public function getCreditCardDetails(): ? array
    {
        if (!$this->creditCard) {
            return [];
        }

        return ["credit_card" => $this->creditCard->getDetailsToExpense()];
    }

    public function getFullData(): array
    {
        $data = [
            "id" => $this->id,
            "place" => $this->getPlaceIdDescription(),
            "description" => $this->description,
            "registered_by" => $this->getNameAndIdUser($this->registered_by),
            "category" => $this->getCategoryDetails(),
            "value" => $this->value,
            "amount_paid"  => $this->amount_paid,
            "payment_type" => $this->payment_type,
            "status" => $this->status,
            "payment_type_description"  => GeneralTypes::getPaymentsDescription($this->payment_type),
            "status_description" => GeneralTypes::getExpenseDescription($this->status),
            "installment_number" => $this->installment_number,
            "total_installments" => $this->total_installments,
            "paid_by" => $this->getNameAndIdUser($this->paid_by),
            "paid_at" => $this->getDateTimeStringFrom('paid_at'),
            "due_date" => $this->getDateTimeStringFrom('due_date', 'Y-m-d'),
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "updated_at" => $this->getDateTimeStringFrom( 'updated_at'),
            "token_installment_group" => $this->token_installment_group
        ];

        if ($this->creditCard) {
            $data = array_merge($data, $this->getCreditCardDetails());
        }

        return $data;
    }

    public function isToDivideThisExpense(): bool
    {
        if ($this->total_installments > self::MIN_INSTALLMENT) {
            return true;
        }

        return false;
    }

    public function getParamsToListAll(array $requestParams): array
    {
        return $this->extractParamsFromRequest($this->paramsListAll, $requestParams);
    }

    public function getTotalTimesToDivideThisExpense(): ?int
    {
        return $this->total_installments;
    }

    public function startMultipleInstallmentsOfThisExpense(): void
    {
        $this->installment_number = self::FIRST_INSTALLMENT;
    }

    public function setValueDividedByEachInstallment(): void
    {
        if ($this->total_installments < self::MIN_INSTALLMENT) {
            throw new \DivisionByZeroError("Total Installment must be greater than zero");
        }

        if ($this->value > self::VALUE_MUST_BE_GREATER_THAN) {
            $this->value = round($this->value / $this->total_installments, self::ROUND_PRECISION);
        }
    }

    public function setInstallmentNumber(int $number): void
    {
        $this->installment_number = $number;
    }

    public function setRegisteredBy(?User $user): void
    {
        $this->registered_by = $user;
    }

    public function getRegisteredBy(): ? User
    {
        return $this->registered_by;
    }

    public function getDescription(): string
    {
        return (string)$this->description;
    }

    public function setTokenInstallmentGroup(?string $token): void
    {
        $this->token_installment_group = $token;
    }

    public function getGeneratedTokenInstallmentGroupUsing(TokenGeneratorInterface $tokenGenerator): string
    {
        $this->token_installment_group = $tokenGenerator->generate(self::LENGTH_STRING_TOKEN);

        return $this->token_installment_group;
    }

    public function thisExpenseIsPartOfGroup(): bool
    {
        if ($this->token_installment_group) {
            return true;
        }

        return false;
    }

    public function jsonSerialize()
    {
        return $this->getFullData();
    }

    public function getAllAttributesDateAndFormat(): array
    {
        return [
            "due_date" => [
                "format" => "Y-m-d",
                "message" => "Due date invalid, should be in format Y-m-d"
            ],
            "paid_at" => [
                "format" => "Y-m-d H:i:s",
                "message" => "Paid at invalid, should be in format Y-m-d H:i:s"
            ]
        ];
    }

    public function setDueDate(?\DateTimeInterface $dateTime)
    {
        $this->due_date = $dateTime;
    }

    public function getTokenInstallmentGroup(): string
    {
        return $this->token_installment_group ?? '';
    }

    public function getDeletedDateString(): string
    {
        if (!$this->deleted_at) {
            return (string)$this->deleted_at;
        }

        return $this->getDateTimeStringFrom('deleted_at');
    }

    public function paidNowBy(?User $user): void
    {
        $this->status = GeneralTypes::STATUS_PAID;
        $this->amount_paid = $this->value;
        $this->paid_by = $user;
        $this->paid_at = new \DateTime();
    }

    public function fixTotalInstallmentIfCash(): void
    {
        if ($this->payment_type === GeneralTypes::STATUS_PAYMENT_CASH) {
            $this->total_installments = self::MIN_INSTALLMENT;
        }
    }

    public function getValue():float
    {
        if (!$this->value) {
            return 0;
        }
        return $this->value;
    }

    public function getCategoryDetails(): ?array
    {
        if (!$this->category) {
            return $this->category;
        }

        return $this->category->getCategoryInfo();
    }

    public function getFieldsNotAllowedToChange(): ?array
    {
        return $this->fieldsMustNotChange;
    }
}
