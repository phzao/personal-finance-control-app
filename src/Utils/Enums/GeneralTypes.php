<?php

namespace App\Utils\Enums;

use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @package App\Utils\Enums
 */
class GeneralTypes
{
    const STATUS_ENABLE  = "enable";
    const STATUS_BLOCKED = "blocked";
    const STATUS_DISABLE = "disable";

    const DEFAULT_SET = true;
    const DEFAULT_UNSET = false;

    const STATUS_PENDING  = "pending";
    const STATUS_PAID     = "paid";
    const STATUS_OVERDUE  = "overdue";
    const STATUS_CANCELED = "canceled";

    const STATUS_PAYMENT_BANK_TRANSFER = "bank_transfer";
    const STATUS_PAYMENT_CREDIT_CARD = "credit_card";
    const STATUS_PAYMENT_CASH = "cash";
    const STATUS_PAYMENT_CHECK = "check";
    const STATUS_PAYMENT_CREDIT = "credit";
    const STATUS_PAYMENT_MILES = "miles";
    const STATUS_PAYMENT_BOLETO = "boleto";
    const STATUS_PAYMENT_PROMISSORIA = "promissoria";
    const STATUS_PAYMENT_DUPLICATA = "duplicata";

    const TYPE_SALARY = "salary";
    const TYPE_MOONLIGHTING = "moonlighting";
    const TYPE_MONTHLY_ALLOWANCE = "monthly_allowance";

    const BANNER_VISA = "visa";
    const BANNER_MASTERCARD = "mastercard";
    const BANNER_DINERS = "diners";
    const BANNER_ELO = "elo";
    const BANNER_AMEX = "amex";
    const BANNER_DISCOVER = "aura";
    const BANNER_JCB = "jcb";
    const BANNER_HIPERCARD = "hipercard";

    const TYPE_EARN_LIST = [
        self::TYPE_SALARY,
        self::TYPE_MONTHLY_ALLOWANCE,
        self::TYPE_MOONLIGHTING
    ];

    const BANNER_DEFAULT_LIST = [
        self::BANNER_VISA,
        self::BANNER_MASTERCARD,
        self::BANNER_DINERS,
        self::BANNER_AMEX,
        self::BANNER_ELO,
        self::BANNER_DISCOVER,
        self::BANNER_JCB,
        self::BANNER_HIPERCARD
    ];

    const STATUS_DEFAULT_LIST = [
        self::STATUS_ENABLE,
        self::STATUS_DISABLE,
        self::STATUS_BLOCKED
    ];

    const STATUS_EXPENSE_LIST = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_OVERDUE,
        self::STATUS_CANCELED,
    ];

    const STATUS_PAYMENT_LIST = [
        self::STATUS_PAYMENT_BANK_TRANSFER,
        self::STATUS_PAYMENT_CREDIT,
        self::STATUS_PAYMENT_CREDIT_CARD,
        self::STATUS_PAYMENT_CASH,
        self::STATUS_PAYMENT_CHECK,
        self::STATUS_PAYMENT_MILES,
        self::STATUS_PAYMENT_DUPLICATA,
        self::STATUS_PAYMENT_PROMISSORIA,
        self::STATUS_PAYMENT_BOLETO
    ];

    const DEFAULT_SETTING_DESCRIPTION = [
        self::DEFAULT_UNSET=>"Não padrão",
        self::DEFAULT_SET=>"Padrão",
    ];

    const STATUS_PAYMENT_DESCRIPTION = [
        self::STATUS_PAYMENT_BANK_TRANSFER => "transferência bancária",
        self::STATUS_PAYMENT_CREDIT_CARD => "cartão de crédito",
        self::STATUS_PAYMENT_CREDIT => "credito",
        self::STATUS_PAYMENT_CASH => "dinheiro",
        self::STATUS_PAYMENT_CHECK => "check",
        self::STATUS_PAYMENT_MILES => "milhas",
        self::STATUS_PAYMENT_PROMISSORIA => "promissória",
        self::STATUS_PAYMENT_DUPLICATA=> "duplicata",
        self::STATUS_PAYMENT_BOLETO => "boleto",
    ];

    const CARD_BANNER_DESCRIPTION = [
        self::BANNER_VISA => "Visa",
        self::BANNER_MASTERCARD => "Mastercard",
        self::BANNER_DINERS => "Diners",
        self::BANNER_AMEX => "Amex",
        self::BANNER_ELO => "Elo",
        self::BANNER_DISCOVER => "Discover",
        self::BANNER_JCB => "Jcb",
        self::BANNER_HIPERCARD => "Hipercard"
    ];

    const TYPE_EARN_DESCRIPTION = [
      self::TYPE_MOONLIGHTING => "Ganho Extra",
      self::TYPE_SALARY => "Salário",
      self::TYPE_MONTHLY_ALLOWANCE => "Mesada",

    ];

    const STATUS_EXPENSE_DESCRIPTION = [
        self::STATUS_PAID => "pago",
        self::STATUS_PENDING => "pendente",
        self::STATUS_OVERDUE => "em atraso",
        self::STATUS_CANCELED => "cancelada"
    ];

    const STATUS_DESCRIPTION = [
        self::STATUS_ENABLE  => "ativo",
        self::STATUS_DISABLE => "inativo"
    ];

    static public function getStatusList(): array
    {
        return self::STATUS_DEFAULT_LIST;
    }

    static public function getExpenseStatusList(): array
    {
        return self::STATUS_EXPENSE_LIST;
    }

    static public function getPaymentStatusList(): array
    {
        return self::STATUS_PAYMENT_LIST;
    }

    static public function getDefaultDescription(string $key): string
    {
        return (new self)->getDescription($key, self::STATUS_DESCRIPTION);
    }

    static public function getDefaultSettingDescription(bool $key): string
    {
        if ($key) {
            return self::DEFAULT_SETTING_DESCRIPTION[self::DEFAULT_SET];
        }

        return self::DEFAULT_SETTING_DESCRIPTION[self::DEFAULT_UNSET];
    }

    static public function getPaymentsDescription(string $key): string
    {
        return (new self)->getDescription($key, self::STATUS_PAYMENT_DESCRIPTION);
    }

    static public function getTypeEarnDescription(string $key): string
    {
        return (new self)->getDescription($key, self::TYPE_EARN_DESCRIPTION);
    }

    static public function getCardBannerDescription(string $key): string
    {
        return (new self)->getDescription($key, self::CARD_BANNER_DESCRIPTION);
    }

    static public function getExpenseDescription(string $key): string
    {
        return (new self)->getDescription($key, self::STATUS_EXPENSE_DESCRIPTION);
    }

    public function getDescription(string $key, array $list): string
    {
        if (!array_key_exists($key, $list)) {
            return $key;
        }

        return $list[$key];
    }

    static public function getStatusDescriptionList(): array
    {
        return self::STATUS_DESCRIPTION;
    }

    static public function isValidDefaultStatusOrFail(string $status)
    {
        $list = self::STATUS_DEFAULT_LIST;
        (new self)->isValidStatusOrFail($status, $list);
    }

    public function isValidStatusOrFail(string $status, array $list): ? bool
    {
        if (!in_array($status, $list)) {

            $list = ["status" => "This status $status is invalid!"];
            $msg  = ErrorMessage::getArrayMessageToJson($list);

            throw new UnprocessableEntityHttpException($msg);
        }

        return true;
    }
}