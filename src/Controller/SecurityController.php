<?php

namespace App\Controller;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\User;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(IriConverterInterface $iriConverter, #[CurrentUser] ?User $user = null): Response
    {
        if ($user === null) {
            return $this->json([
                'error' => 'Invalid login request. Check that the Content-Type header is "application/json"',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new Response(null, Response::HTTP_NO_CONTENT, [
            'Location' => $iriConverter->getIriFromResource($user),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new Exception('This should never be reached!');
    }
}