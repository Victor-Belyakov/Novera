<?php

namespace App\User\Controller;

use App\User\Application\DTO\UserUpdateRequestDto;
use App\User\Domain\Service\UserServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    public function __construct(
        private UserServiceInterface $userService,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/users', methods: ['GET'])]
    public function listUsers(Request $request): Response
    {
        $fio = $request->query->get('fio');
        $phone = $request->query->get('phone');
        $sortBy = $request->query->get('sort_by', 'id');
        $sortOrder = $request->query->get('sort_order', 'ASC');

        $list = $this->userService->getList(
            $fio !== null && $fio !== '' ? (string) $fio : null,
            $phone !== null && $phone !== '' ? (string) $phone : null,
            (string) $sortBy,
            (string) $sortOrder
        );

        return $this->json($list, Response::HTTP_OK);
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

    #[Route('/user', methods: ['PATCH'])]
    public function updateCurrentUser(Request $request): Response
    {
        /** @var UserEntity|null $user */
        $user = $this->getUser();

        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            /** @var UserUpdateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                UserUpdateRequestDto::class,
                'json'
            );

            $updated = $this->userService->update($user, $dto);
            return $this->json($updated, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
