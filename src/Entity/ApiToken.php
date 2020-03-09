<?php

namespace App\Entity;

use App\Entity\Interfaces\ApiTokenInterface;
use App\Entity\Traits\SimpleTime;
use App\Utils\Generators\TokenGeneratorInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="api_tokens",
 *     indexes={
 *     @ORM\Index(name="apitokens_users_expired_at_idx", columns={"user_id", "expired_at"}),
 *     @ORM\Index(name="apitokens_users_idx", columns={"user_id"}),
 * })
 */
class ApiToken implements ApiTokenInterface
{
    use SimpleTime;

    const LIMIT_BY_MINUTE = 50;
    const LIMIT_BY_HOUR = 1000;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $token;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $expire_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $expired_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

//    /**
//     * @ORM\Column(type="datetime", nullable=true)
//     */
//    protected $last_minute;
//
//    /**
//     * @ORM\Column(type="datetime", nullable=true)
//     */
//    protected $last_hour;
//
//    /**
//     * @ORM\Column(type="integer", options={"default": 0})
//     */
//    protected $count_last_hour;
//
//    /**
//     * @ORM\Column(type="integer", options={"default": 0})
//     */
//    protected $count_last_minute;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="apiTokens")
     * @ORM\JoinColumn(referencedColumnName="id" ,nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->expire_at = new \DateTime('+30 days');
        $this->created_at = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getExpireAt(): ?\DateTimeInterface
    {
        return $this->expire_at;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function generateToken(TokenGeneratorInterface $tokenGenerator): void
    {
        $this->token = $tokenGenerator->generate(125);
    }

    public function setUser(?User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUserData(): array
    {
        if (empty($this->user)) {
            return [];
        }
        return $this->user->getFullData();
    }
//
//    public function getTimeLastAccess():?\DateTimeInterface
//    {
//        return $this->last_minute;
//    }
//
//    public function addCountAccess()
//    {
//        $this->count_last_minute++;
//    }
//
//    public function zeroLastMinuteCount()
//    {
//        $this->count_last_minute = 0;
//    }
//
//    public function itIsLooksLikeDDOSByMinute(): bool
//    {
//        return $this->count_last_minute > self::LIMIT_BY_MINUTE;
//    }
//
//    public function itIsLooksLikeDDOSByHour(): bool
//    {
//        return $this->count_last_minute > self::LIMIT_BY_HOUR;
//    }

    public function getDetailsToken(): array
    {
        return [
            "id" => $this->id,
            "token" => $this->token,
            "user" => $this->getUserData(),
            "logged_at" => $this->getDateTimeStringFrom("created_at"),
            "expire_at" => $this->getDateTimeStringFrom("expire_at"),
            "expired_at" => $this->getDateTimeStringFrom("expired_at")
        ];
    }

    /**
     * @throws \Exception
     */
    public function invalidateToken():void
    {
        $this->expired_at = new \DateTime("now");
    }
}
