<?php

namespace App\Auth\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Auth\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Auth\Infrastructure\Security\RefreshTokenGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Exception;

#[Route('/auth/refresh', methods: ['POST'])]
final class RefreshTokenController
{
    /**
     * @param Request $request
     * @param RefreshTokenRepositoryInterface $refreshTokens
     * @param JWTTokenManagerInterface $jwtManager
     * @param RefreshTokenGenerator $generator
     * @param Security $security
     * @return JsonResponse
     */
    public function __invoke(
        Request $request,
        RefreshTokenRepositoryInterface $refreshTokens,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenGenerator $generator,
        Security $security
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            $token = $data['refresh_token'] ?? null;

            if (!$token) {
                return new JsonResponse(['error' => 'refresh_token required'], 400);
            }

            $refresh = $refreshTokens->findByToken($token);

            if (!$refresh || $refresh->isExpired()) {
                return new JsonResponse(['error' => 'invalid refresh token'], 401);
            }

            $user = $refresh->getUser();

            $refreshTokens->remove($refresh);

            $newRefresh = $refreshTokens->createForUser($user, '+14 days');

            return new JsonResponse([
                'token' => $jwtManager->create($user),
                'refresh_token' => $newRefresh->getToken(),
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
