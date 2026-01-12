<?php

namespace App\Auth\Controller;

use App\Auth\Application\DTO\RegisterRequestDto;
use App\Auth\Application\Service\RegisterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegisterService $registerService,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/auth/register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var RegisterRequestDto $dto */
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            RegisterRequestDto::class,
            'json',
            [
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d',
            ]
        );

        $this->registerService->register($dto);

        return new JsonResponse(['status' => 'ok'], Response::HTTP_CREATED);
    }
}
