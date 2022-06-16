<?php

namespace App\Service;

use App\Repository\UserRepository;
use Carbon\Carbon;

class CreditOperationService
{
    const USER_ACCOUNT_BALANCE_MAX = 1000;

    private UserRepository $userRepository;
    private EmailSenderService $emailSenderService;

    public function __construct(UserRepository $userRepository, EmailSenderService $emailSenderService) {
        $this->userRepository = $userRepository;
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
        }
        else {
            $user->setBankAccount(self::USER_ACCOUNT_BALANCE_MAX);
            $creditDetails['balance'] = self::USER_ACCOUNT_BALANCE_MAX;
            $creditDetails['refund'] = $remaining;
        }
        
        if($carbon->hour >= 22 || $carbon->hour <= 6){
            $this->emailSenderService->sendEmail($user->getEmail(), 'Credit operation');
        }

        return $creditDetails;
    }
}