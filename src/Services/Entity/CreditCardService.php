<?php

namespace App\Services\Entity;

use App\Entity\CreditCard;
use App\Entity\User;
use App\Repository\Interfaces\CreditCardRepositoryInterface;
use App\Services\Entity\Interfaces\CreditCardServiceInterface;
use App\Utils\Enums\GeneralTypes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CreditCardService implements CreditCardServiceInterface
{
    const LAST_DIGITS_DEFAULT = 9999;
    const BANNER_DEFAULT = 'visa';
    /**
     * @var CreditCardRepositoryInterface
     */
    private $repository;

    /**
     * @throws \Exception
     */
    public function __construct(CreditCardRepositoryInterface $creditCard)
    {
        $this->repository = $creditCard;
    }

    public function getListAllByUser(string $user_id): array
    {
        $parameter["user"] = $user_id;
        return $this->repository->getAllByAndOrderedBy($parameter);
    }

    public function getCreditCardFromUserByIdOrFail(string $user_id, string $uuid): ? CreditCard
    {
        $creditCard = $this->repository->getOneByUserAndID($user_id, $uuid);

        if (!$creditCard) {
            throw new NotFoundHttpException("There is no credit card with this $uuid");
        }

        return $creditCard;
    }

    public function updateStatus(CreditCard $creditCard, string $status)
    {
        $creditCard->changeStatusAndSetNoDefaultIfNecessary($status);

        $this->repository->save($creditCard);
    }

    public function updateStatusDefaultSetting(CreditCard $creditCard, string $uuid)
    {
        $this->repository->setAllCreditCardsAsNonDefault($uuid);
        $creditCard->setDefaultAndEnableIfIsDisable();

        $this->repository->save($creditCard);
    }

    public function logicDelete(CreditCard $creditCard): void
    {
        $creditCard->remove();

        $this->repository->save($creditCard);
    }

    /**
     * @throws \Exception
     */
    public function getOneAnywayIfExpenseIsOfTypeCreditCard(?User $user, array $request):?CreditCard
    {
        $creditCard = null;

        if (empty($request["payment_type"])) {
            return $creditCard;
        }

        if ($request["payment_type"]!==GeneralTypes::STATUS_PAYMENT_CREDIT_CARD) {
            return $creditCard;
        }

        if (!empty($request["credit_card_id"])) {
            $creditCard = $this->repository->getOneById($request["credit_card_id"]);
        }

        if (!$creditCard) {
            $creditCard = $this->repository->getOneDefaultOrNotByUser($user->getId());
        }

        if (!$creditCard) {
            $creditCard = new CreditCard();
            $creditCard->setUser($user);

            $data["last_digits"] = self::LAST_DIGITS_DEFAULT;
            $data["card_banner"] = self::BANNER_DEFAULT;

            $creditCard->setAttributes($data);

            $this->repository->save($creditCard);
        }

        return $creditCard;
    }

    public function getNotDeletedListByUser(string $user_id): array
    {
        $parameters = [
            "user" => $user_id,
            "deleted_at" => null
        ];

        return $this->repository->getAllNotDeletedAndOrderedBy($parameters);
    }

    /**
     * @throws \Exception
     */
    public function createOrLoadCreditCard(?User $user, array $request): array
    {
        if (empty($request["credit_card_id"])) {
            return $request;
        }

        $request["credit_card"] = $this->getOneAnywayIfExpenseIsOfTypeCreditCard($user, $request);
    }
}