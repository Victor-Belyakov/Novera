<?php

namespace App\Auth\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Auth\Domain\Repository\RefreshTokenRepositoryInterface;

#[Route('/auth/logout', methods: ['POST'])]
final class LogoutController
{
    /**
     * @param Security $security
     * @param RefreshTokenRepositoryInterface $refreshTokens
     * @return JsonResponse
     */
    public function __invoke(
        Security $security,
        RefreshTokenRepositoryInterface $refreshTokens
    ): JsonResponse {
        $user = $security->getUser();

        if ($user) {
            $refreshTokens->removeAllForUser($user);
        }

        return new JsonResponse(['status' => 'logged_out']);
    }
}
