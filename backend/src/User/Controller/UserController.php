<?php

namespace App\User\Controller;

use App\User\Domain\Service\UserServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function __construct(
        private UserServiceInterface $userService
    ) {
    }

    #[Route('/user', methods: ['GET'])]
    public function getCurrentUser(): Response
    {
        $statusCode = Response::HTTP_OK;

        try {
            /** @var UserEntity|null $user */
            $user = $this->getUser();

            if (!$user instanceof UserEntity) {
                return $this->json(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
            }

            $userDto = $this->userService->getUser($user->getId());

            if (!$userDto) {
                return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            $data = $userDto;
        } catch (\Exception $e) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $data = ['error' => $e->getMessage()];
        }

        return $this->json($data, $statusCode);
    }
}
