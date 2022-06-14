<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users')]
class UserController extends AbstractController
{
    #[Route('/{id}/accounts', methods: ['GET'])]
    public function getBankAccount(User $user): JsonResponse
    {
        return $this->json(["balance" => 1000]);
    }

    #[Route('/{id}/accounts/credit', methods: ['PUT'])]
    public function credit(User $user): JsonResponse
    {
        return $this->json([
            "credit" => 10,
            "balance" => 200
        ]);
    }

    #[Route('/{id}/accounts/debit', methods: ['PUT'])]
    public function debit(User $user): JsonResponse
    {
        return $this->json([
            "debit" => 20,
            "balance" => 210
        ]);
    } 

}