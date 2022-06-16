<?php

namespace App\Test;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CreditOperationService;
use App\Service\EmailSenderService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class CreditOperationServiceTest extends TestCase
{
    const USER_ACCOUNT_BALANCE_MAX = 1000;
    const USER_ACCOUNT_BALANCE = self::USER_ACCOUNT_BALANCE_MAX - 300;
    
    private Carbon $carbon;
    private $userRepositoryMock;
    private $entityManagerMock;
    private $emailSenderService;
    private CreditOperationService $creditOperationService;
    private User $user;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();

        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
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

    public function testSuccessful()
    {
        $this->creditOperationService = new CreditOperationService($this->userRepositoryMock, $this->emailSenderService);
        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);
        
        $userId = 1;
        $amount = 150;

        $this->assertEquals([
            "balance" => self::USER_ACCOUNT_BALANCE + $amount,
            "credit" => $amount,
            "refund" => 0
        ], $this->creditOperationService->credit($amount, $userId, $this->carbon));
    }

    public function testUserBankAccountBalanceSetsWhenOperationSuccessful()
    {
        $this->creditOperationService = new CreditOperationService($this->userRepositoryMock, $this->emailSenderService);
        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);
        
        $userId = 1;
        $amount = 150;
        $this->creditOperationService->credit($amount, $userId, $this->carbon);

        $this->assertEquals(self::USER_ACCOUNT_BALANCE + $amount, $this->user->getBankAccount());
    }

    public function testRefundAmountDueToBalanceLimitReached()
    {
        $this->creditOperationService = new CreditOperationService($this->userRepositoryMock, $this->emailSenderService);
        
        $this->user->setBankAccount(self::USER_ACCOUNT_BALANCE_MAX);
        
        $userId = 1;
        $amount = 150;

        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);

        $this->assertEquals([
            "balance" => self::USER_ACCOUNT_BALANCE_MAX,
            "credit" => $amount,
            "refund" => $amount
        ], $this->creditOperationService->credit($amount, $userId, $this->carbon));
    }

    public function testRefundRemainingAmountDueToBalanceLimitReached()
    {
        $this->creditOperationService = new CreditOperationService($this->userRepositoryMock, $this->emailSenderService);
        
        $userId = 1;
        $amount = 400;

        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);

        $this->assertEquals([
            "balance" => self::USER_ACCOUNT_BALANCE_MAX,
            "credit" => $amount,
            "refund" => (self::USER_ACCOUNT_BALANCE + $amount) - self::USER_ACCOUNT_BALANCE_MAX
        ], $this->creditOperationService->credit($amount, $userId, $this->carbon));
    }

    public function testUserBankAccountBalanceSetsWhenBalanceLimitReached()
    {
        $this->creditOperationService = new CreditOperationService($this->userRepositoryMock, $this->emailSenderService);
        
        $userId = 1;
        $amount = 400;

        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);

        $this->creditOperationService->credit($amount, $userId, $this->carbon);
        
        $this->assertEquals(self::USER_ACCOUNT_BALANCE_MAX, $this->user->getBankAccount());
    }

    public function testUserObjectPersistedTODbWhenOperationSuccesfull()
    {
        $this->creditOperationService = new CreditOperationService($this->userRepositoryMock, $this->emailSenderService);
        
        $userId = 1;
        $amount = 400;

        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);
        
        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $this->creditOperationService->credit($amount, $userId, $this->carbon);
        
    }

    // if credit operation between 10pm and 6am, should send email
    public function testSendingEmailIfOperationInTimeInterval()
    {
        $this->creditOperationService = new CreditOperationService($this->userRepositoryMock, $this->emailSenderService);

        $userId = 1;
        $amount = 150;
        
        $this->carbon->setHour(3);

        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($this->user);
        
        $this->emailSenderService->expects($this->once())
            ->method('sendEmail');
        
        $this->creditOperationService->credit($amount, $userId, $this->carbon);
    }

    // if credit operation is not between 10pm and 6am, don't send email
    public function testNotSendingEmailIfOperationOutOfTimeInterval()
    {
        $this->creditOperationService = new CreditOperationService($this->userRepositoryMock, $this->emailSenderService);

        $userId = 1;
        $amount = 150;
        
        $this->carbon->setHour(15);

        $this->userRepositoryMock->expects($this->once())
        ->method('find')
        ->willReturn($this->user);
    
        $this->emailSenderService->expects($this->never())
            ->method('sendEmail');

        $this->creditOperationService->credit($amount, $userId, $this->carbon);
    }
}