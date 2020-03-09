<?php

namespace App\Services\Entity;

use App\Entity\Earn;
use App\Repository\Interfaces\EarnRepositoryInterface;
use App\Services\Entity\Interfaces\EarnServiceInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EarnService implements EarnServiceInterface
{
    /**
     * @var EarnRepositoryInterface
     */
    private $repository;

    /**
     * @throws \Exception
     */
    public function __construct(EarnRepositoryInterface $creditCard)
    {
        $this->repository = $creditCard;
    }

    public function getListAllByUser(string $user_id): array
    {
        return $this->repository->getAllByUserOrderedBy($user_id);
    }

    /**
     * @throws ConversionException
     */
    public function getEarnFromUserByIdOrFail(string $user_id, string $uuid): ?Earn
    {
        try {
            $earn = $this->repository->getOneByUserAndID($user_id, $uuid);
        } catch (DBALException $e) {
            throw new ConversionException("This id $uuid is invalid!");
        }

        if (!$earn) {
            throw new NotFoundHttpException("There is no earn with this $uuid");
        }

        return $earn;
    }
}