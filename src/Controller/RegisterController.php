<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Services\Entity\Interfaces\UserServiceInterface;
use App\Services\External\Google\GoogleCheckServiceInterface;
use App\Services\Log\Interfaces\LoggerServiceInterface;
use App\Services\Login\LoginServiceInterface;
use App\Services\Validation\ValidationService;
use App\Utils\Generators\GenerateDataToSaveInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @package App\Controller
 */
class RegisterController extends APIController
{
    /**
     * @var UserRepositoryInterface
     */
    private $repository;

    public function __construct(UserRepositoryInterface $userRepository,
                                LoggerServiceInterface $loggerService)
    {
        parent::__construct($loggerService);
        $this->repository = $userRepository;
    }

    /**
     * @Route("/register", methods={"POST"})
     * @throws \Exception
     */
    public function save(Request $request,
                         ValidationService $validationService,
                         UserPasswordEncoderInterface $passwordEncoder)
    {
        try {

            $data = $request->request->all();
            $user = new User();

            $user->setAttributes($data);

            $validationService->entityIsValidOrFail($user);

            $user->encryptPassword($passwordEncoder);

            $this->repository->save($user);

            return $this->respondCreated($user->getFullData());

        } catch(UniqueConstraintViolationException $PDOException){

            $this->logger->error($PDOException->getMessage());
            return $this->respondNotAllowedError("Email already in use! Use another one!");
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/google-authenticate", methods={"POST"})
     * @throws \Exception
     */
    public function loginGoogle(Request $request,
                                GoogleCheckServiceInterface $googleCheckService,
                                UserServiceInterface $userService,
                                LoginServiceInterface $loginService)
    {
        try {
            $data = $request->request->all();
            $googleCheckService->requestHasNameEmailAndAccessTokenOrFail($data);
            $googleCheckService->isValidGoogleAccessTokenOrFail($data);

            $user = $userService->getUserByEmailAnyway($data);

            $loginData = $loginService->getTokenCreateIfNotExist($user);

            return $this->respondSuccess($loginData->getDetailsToken());

        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/authenticate-demo", methods={"POST"})
     * @throws \Exception
     */
    public function saveDemo(GenerateDataToSaveInterface $generator,
                             LoginServiceInterface $loginService,
                             UserPasswordEncoderInterface $passwordEncoder)
    {
        try {

            $data = $generator->getEmailNameAndPassword();
            $user = new User();

            $user->setAttributes($data);
            $user->encryptPassword($passwordEncoder);

            $this->repository->save($user);

            $loginData = $loginService->getTokenCreateIfNotExist($user);

            return $this->respondSuccess($loginData->getDetailsToken());

        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/authenticate", methods={"POST"})
     */
    public function login(Request $request,
                          UserServiceInterface $userService,
                          LoginServiceInterface $loginService)
    {
        try {
            $data = $request->request->all();

            $loginService->requestShouldHaveEmailAndPasswordOrFail($data);

            $user = $userService->getUserByEmailToLoginOrFail($data["email"]);

            $loginService->userShouldCanAuthenticateOrFail($user);
            $loginService->passwordShouldBeRightOrFail($user, $data["password"]);

            $loginData = $loginService->getTokenCreateIfNotExist($user);

            return $this->respondSuccess($loginData->getDetailsToken());

        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch(ForbiddenOverwriteException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (BadCredentialsException $exception) {

            return $this->respondInvalidCredentialsFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}