<?php

namespace App\PersonalState\Controller;

use App\PersonalState\Domain\Service\PersonalStateServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/personal-state')]
class PersonalStateController extends AbstractController
{
    public function __construct(
        private PersonalStateServiceInterface $personalStateService,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function show(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($this->personalStateService->getByUser($user), Response::HTTP_OK);
    }
}
