<?php

namespace App\Service;

use App\Repository\UserRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;

class DebitOperationService
{
    const USER_ACCOUNT_BALANCE_MIN = 0;

    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private EmailSenderService $emailSenderService;

    public function __construct(UserRepository $userRepository, EmailSenderService $emailSenderService, EntityManagerInterface $entityManager) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->emailSenderService = $emailSenderService;
    }

    public function debit(int $amount, int $userId, Carbon $carbon): ?array
    {
        if ($amount == null) {
            return null;
        }
        if (!is_integer($amount)) {
            return "Montant doit être un nombre entier";
        }
        $user = $this->userRepository->find($userId);

        $balance = $user->getBankAccount();
        $debited = false;

        $amountTemporary = $amount;
        if (($balance - $amount) >= self::USER_ACCOUNT_BALANCE_MIN) {
            $amountTemporary = $amount;
            $debited = true;
            
        } else {
            $amountTemporary = $balance - self::USER_ACCOUNT_BALANCE_MIN;
        }

        $creditBankAccount = $user->setBankAccount($balance - $amountTemporary);

        $this->entityManager->flush();

        if($carbon->hour >= 22 || $carbon->hour <= 6){
            $this->emailSenderService->sendEmail($user->getEmail(), 'Debit operation');
        }

        return [
            "balance" => $creditBankAccount->getBankAccount(),
            "debit" => $amount,
            "debited" => $debited,
        ];
    }
}