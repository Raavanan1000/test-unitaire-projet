<?php


namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $userRepository;
    private $entityManager;
    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }
    public function credit(int $amount, int $userId): ?array
    {
        if ($amount == null) {
            return null;
        }
        if (!is_integer($amount)) {
            return "Montant doit être un nombre entier";
        }

        $maxAmount = 1000;
        $balance = $this->getBalance($userId);
        $refundAmount = 0;

        $amountTemporary = $amount;
        if (($maxAmount - $balance) > $amount) {
            $amountTemporary = $amount;
        } else {
            $amountTemporary = $maxAmount - $balance;
            $refundAmount = $amount - ($maxAmount - $balance);
        }

        $creditBankAccount = $this->userRepository->find($userId)->setBankAccount($balance + $amountTemporary);

        $this->entityManager->flush();

        return [
            "balance" => $creditBankAccount->getBankAccount(),
            "credit" => $amount,
            "refund" => $refundAmount,
        ];
    }

    public function debit(int $amount, int $userId): ?array
    {
        if ($amount == null) {
            return null;
        }
        if (!is_integer($amount)) {
            return "Montant doit être un nombre entier";
        }

        $maxAmount = 0;
        $balance = $this->getBalance($userId);
        $debited = false;

        $amountTemporary = $amount;
        if (($balance - $amount) >= $maxAmount) {
            $amountTemporary = $amount;
            $debited = true;
            
        } else {
            $amountTemporary = $balance - $maxAmount;
        }

        $creditBankAccount = $this->userRepository->find($userId)->setBankAccount($balance - $amountTemporary);

        $this->entityManager->flush();

        return [
            "balance" => $creditBankAccount->getBankAccount(),
            "debit" => $amount,
            "debited" => $debited,
        ];
    }

    public function getUser(int $userId): ?User
    {
        if ($userId == null) {
            return null;
        }
        $user = $this->userRepository->find($userId);
        if ($user == null) {
            return "L'utilisateur n'existe pas";
        }

        return $this->userRepository->find($userId);
    }

    public function getBalance(int $userId): ?int
    {
        if ($userId == null) {
            return null;
        }
        $user = $this->userRepository->find($userId);
        if ($user == null) {
            return "L'utilisateur n'existe pas";
        }

        return $this->userRepository->find($userId)->getBankAccount();
    }

    public function balanceIsValid(int $balance): ?int
    {
        $maxAmount = 1000;

        if ($balance >= null) {
            if ($maxAmount>= $balance ) {
            return true;
        } else {
            return false;
        }
        }else{
            return false;
        }
        
    }
}

