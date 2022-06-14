<?php


namespace App\Service;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class UserService {
    private $userRepository;
    private $entityManager;
    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }
    public function credit(int $montant, int $userId) : ?int
    {
        if ($montant == null) {
            return null;
        }
        if (!is_integer($montant)) {
            return "Montant doit être un nombre entier";
        }

        $balance = $this->getBalance($userId);

        if($balance < 10000){
            $creditBankAccount = $this->userRepository->find($userId)->setBankAccount($montant);
            $this->entityManager->flush();
        }
        
        return $balance;
    }

    public function debit(int $montant, int $userId) : ?int
    {
        if ($montant == null) {
            return null;
        }
        if (!is_integer($montant)) {
            return "Montant doit être un nombre entier";
        }

        $balance = $this->getBalance($userId);
        $creditBankAccount = $this->userRepository->creditBankAccount($montant);
        
        return $balance;
    }

    public function getBalance(int $userId) : ?int
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



}
