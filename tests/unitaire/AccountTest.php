<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use App\Service\UserService;
use Carbon\Carbon;

class AccountTest extends TestCase
{

    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();

        $this->user->setEmail('unit@test.esgi');
        $this->user->setFirstName('first_name');
        $this->user->setLastName('last_name');
        $this->user->setBankAccount(900);
        parent::setUp();
    }


    public function testIsValidNominal()
    {
        $this->assertTrue($this->user->isValid());
    }


    public function testNotValidBadEmail()
    {
        $this->user->setEmail('noEmail');
        $this->assertFalse($this->user->isValid());
    }

    public function testNotValidEmptyEmail()
    {
        $this->user->setEmail('');
        $this->assertFalse($this->user->isValid());
    }


    public function testNotValidDueToFName()
    {
        $this->user->setFirstName('');
        $this->assertFalse($this->user->isValid());
    }

    public function testNotValidDueToLName()
    {
        $this->user->setLastName('');
        $this->assertFalse($this->user->isValid());
    }
    public function testMaxBalance()
    {
        $this->user->bankAccount();
        $this->assertFalse($this->user->isValid());
    }
    public function testBalanceIsValid()
    {
        
        $this->assertFalse($userService->balanceIsValid($this->user->getBankAccount()));
    }

}