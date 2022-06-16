<?php

namespace App\Controller;

use App\Service\UserService;
use App\Service\CreditOperationService;
use App\Entity\User;
use App\Service\DebitOperationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Carbon\Carbon;

#[Route('/users')]
class UserController extends AbstractController
{
    #[Route('/{id}/accounts', methods: ['GET'])]
    public function getBankAccount(User $user, UserService  $userService): JsonResponse
    {
        return $this->json($userService->getUser($user->getId()));
    }

    #[Route('/{id}/accounts/credit', methods: ['PUT'])]
    public function credit(User $user, Request $request, CreditOperationService $creditOperationService): JsonResponse
    {
        return $this->json($creditOperationService->credit($request->query->get('amount'), $user->getId(), Carbon::now()));
    }

    #[Route('/{id}/accounts/debit', methods: ['PUT'])]
    public function debit(User $user, Request $request, DebitOperationService $debitOperationService): JsonResponse
    {
        return $this->json($debitOperationService->debit($request->query->get('amount'), $user->getId(), Carbon::now()));
    } 

}