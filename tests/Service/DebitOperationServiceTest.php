<?php

namespace App\Test;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DebitOperationService;
use App\Service\EmailSenderService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class DebitOperationServiceTest extends TestCase
{

    const USER_ACCOUNT_BALANCE_MIN = 0;
    const USER_ACCOUNT_BALANCE = 700;
    
    private Carbon $carbon;
    private $userRepositoryMock;
    private $entityManagerMock;
    private $emailSenderService;
    private DebitOperationService $debitOperationService;
    private User $user;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();

        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['flush'])
            ->getMock();
        
        $this->emailSenderService = $this->getMockBuilder(EmailSenderService::class)
            ->onlyMethods(['sendEmail'])
            ->getMock();

        $now = Carbon::now();
        $this->carbon = Carbon::create($now->year, $now->month, $now->day, 10);

        $this->user = new User();
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');
        $this->user->setEmail('johndoe@gmail.com');
        $this->user->setBankAccount(self::USER_ACCOUNT_BALANCE);

        parent::setUp();
    }

    public function testOperationSuccessful()
    {
        $this->debitOperationService = new DebitOperationService($this->userRepositoryMock, $this->emailSenderService, $this->entityManagerMock);
        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);
        
        $userId = 1;
        $amount = 150;

        $this->assertEquals([
            "balance" => self::USER_ACCOUNT_BALANCE - $amount,
            "debit" => $amount,
            "debited" => true
        ], $this->debitOperationService->debit($amount, $userId, $this->carbon));
    }

    public function testNotDebitingWhenBankAccountBalanceEqualsMinimumBalance()
    {
        $this->debitOperationService = new DebitOperationService($this->userRepositoryMock, $this->emailSenderService, $this->entityManagerMock);

        $this->user->setBankAccount(self::USER_ACCOUNT_BALANCE_MIN);
        $userId = 1;
        $amount = 150;

        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);
        
        $this->assertEquals([
            "balance" => self::USER_ACCOUNT_BALANCE_MIN,
            "debit" => $amount,
            "debited" => false
        ], $this->debitOperationService->debit($amount, $userId, $this->carbon));
    }

    /*
    public function testNotDebitingWhenAmountBiggerThanBanckAccountBalance()
    {
        $this->debitOperationService = new DebitOperationService($this->userRepositoryMock, $this->emailSenderService, $this->entityManagerMock);

        $this->user->setBankAccount(100);
        $userId = 1;
        $amount = 150;

        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);
        
        $this->assertEquals([
            "balance" => 100,
            "debit" => $amount,
            "debited" => false
        ], $this->debitOperationService->debit($amount, $userId, $this->carbon));
    }
    */

    // if debit operation between 10pm and 6am, should send email
    public function testSendingEmailIfOperationInTimeInterval()
    {
        $this->debitOperationService = new DebitOperationService($this->userRepositoryMock, $this->emailSenderService, $this->entityManagerMock);
        
        $userId = 1;
        $amount = 150;
        $this->carbon->setHour(3);
        
        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);
        
        $this->emailSenderService->expects($this->once())
            ->method('sendEmail');
        
        $this->debitOperationService->debit($amount, $userId, $this->carbon);
    }

    // if debit operation is not between 10pm and 6am, don't send email
    public function testNotSendingEmailIfOperationOutOfTimeInterval()
    {
        $this->debitOperationService = new DebitOperationService($this->userRepositoryMock, $this->emailSenderService, $this->entityManagerMock);
        
        $userId = 1;
        $amount = 150;
        $this->carbon->setHour(15);
        
        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);
        
        $this->emailSenderService->expects($this->never())
            ->method('sendEmail');
        
        $this->debitOperationService->debit($amount, $userId, $this->carbon);
    }
}