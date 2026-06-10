<?php

namespace App\Telegram\Controller;

use App\Telegram\Application\DTO\TelegramAuthRequestDto;
use App\Telegram\Application\Service\TelegramService;
use App\User\Infrastructure\Persistence\UserEntity;
use JsonException;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/telegram')]
final class TelegramController extends AbstractController
{
    public function __construct(
        private TelegramService $telegramService,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/connect-link', methods: ['POST'])]
    public function connectLink(): Response
    {
        /** @var UserEntity|null $user */
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            return $this->json(
                $this->telegramService->generateConnectLink($user),
                Response::HTTP_OK,
            );
        } catch (RandomException|\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/webhook', methods: ['POST'])]
    public function webhook(Request $request): JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            if (is_array($payload)) {
                $this->telegramService->handleWebhook($payload);
            }

            return new JsonResponse(['status' => 'ok'], Response::HTTP_OK);
        } catch (JsonException) {
            return new JsonResponse(['status' => 'ignored'], Response::HTTP_OK);
        }
    }

    #[Route('/auth', methods: ['POST'])]
    public function auth(Request $request): Response
    {
        try {
            /** @var TelegramAuthRequestDto $dto */
            $dto = $this->serializer->deserialize($request->getContent(), TelegramAuthRequestDto::class, 'json');

            return $this->json(
                $this->telegramService->authenticateByInitData($dto->init_data),
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }

    #[Route('/config', methods: ['GET'])]
    public function config(): Response
    {
        return $this->json([
            'bot_username' => $this->telegramService->getBotUsername(),
            'mini_app_url' => $this->telegramService->getMiniAppUrl(),
        ], Response::HTTP_OK);
    }
}
