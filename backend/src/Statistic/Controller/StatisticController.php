<?php

namespace App\Statistic\Controller;

use App\Statistic\Domain\Service\StatisticServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/statistics')]
class StatisticController extends AbstractController
{
    public function __construct(
        private StatisticServiceInterface $statisticService,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function overview(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($this->statisticService->getOverviewByUser($user), Response::HTTP_OK);
    }
}
