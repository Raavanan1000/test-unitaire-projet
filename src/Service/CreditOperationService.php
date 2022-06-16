<?php

namespace App\Service;

use App\Repository\UserRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;

class CreditOperationService
{
    const USER_ACCOUNT_BALANCE_MAX = 1000;

    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private EmailSenderService $emailSenderService;

    public function __construct(UserRepository $userRepository, EmailSenderService $emailSenderService, EntityManagerInterface $entityManager) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->emailSenderService = $emailSenderService;
    }

    public function credit(int $amount, int $userId, Carbon $carbon): ?array
    {
        $user = $this->userRepository->find($userId);
        
        $creditDetails = [
            "balance" => $user->getBankAccount(),
            "credit" => $amount,
            "refund" => 0
        ];

        $remaining = ($user->getBankAccount() + $amount) - self::USER_ACCOUNT_BALANCE_MAX;
        if($remaining <= 0) {
            $bankAccountBalance = $user->getBankAccount() + $amount;
            $user->setBankAccount($bankAccountBalance);
            $creditDetails['balance'] = $bankAccountBalance;
            
            $this->entityManager->flush();
        }
        else {
            $user->setBankAccount(self::USER_ACCOUNT_BALANCE_MAX);
            $creditDetails['balance'] = self::USER_ACCOUNT_BALANCE_MAX;
            $creditDetails['refund'] = $remaining;

            $this->entityManager->flush();
        }
        
        if($carbon->hour >= 22 || $carbon->hour <= 6){
            $this->emailSenderService->sendEmail($user->getEmail(), 'Credit operation');
        }

        return $creditDetails;
    }
}