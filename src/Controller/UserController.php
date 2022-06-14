<?php

namespace App\Controller;

use App\Service\UserService;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users')]
class UserController extends AbstractController
{
    #[Route('/{id}/accounts', methods: ['GET'])]
    public function getBankAccount(User $user, UserService  $userService): JsonResponse
    {
        return $this->json([
            'balance' => $userService->getBalance($user->getId()),
        ]);
    }

    #[Route('/{id}/accounts/credit', methods: ['PUT'])]
    public function credit(User $user, Request $request, UserService $userService): JsonResponse
    {
        return $this->json($userService->credit($request->query->get('amount'), $user->getId()));
    }

    #[Route('/{id}/accounts/debit', methods: ['PUT'])]
    public function debit(User $user, Request $request, UserService $userService): JsonResponse
    {
        return $this->json([
            "debit" => $request->query->get('amount'),
            "balance" => $userService->debit($request->query->get('amount'), $user->getId()),
        ]);
    } 

}