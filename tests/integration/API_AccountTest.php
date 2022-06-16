<?php

namespace App\Tests\integration;

use App\Entity\User;
use App\Controller\UserController;
use App\Service\UserService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class API_AccountTest extends TestCase
{
    private User $user;
    private $emailSenderService;

    protected function setUp(): void
    {
        $this->user = new User();

        $this->user->setEmail('unit@test.esgi');
        $this->user->setFirstName('first_name');
        $this->user->setLastName('last_name');
        $this->user->setBankAccount(900);
        $this->emailSenderService = $this->getMockBuilder(EmailSenderService::class)
            ->onlyMethods(['sendEmail'])
            ->getMock();
        parent::setUp();
    }

    public function testGet_account() : void
        $user_test=$userService->getUser($this->user->getId());

        $API_user_json = $this->UserController->getBankAccount($this->user,$userService);

        $this->assertJsonStringEqualsJsonString($API_user_json, serializeToJson($user_test));
    }

    public function testPut_credit() : void
    {
        $request= new Request(["amount"=>"1000"]);
        $credit_test = $userService->credit(1000,$this->user->getId());
        $API_credit_json = $this->UserController->credit($this->user,$request,$userService);

        $this->assertJsonStringEqualsJsonString($API_credit_json, serializeToJson($credit_test));
    }

    public function testPut_debit() : void
    {
        $request= new Request(["amount"=>"1000"]);
        $debit_test = $userService->debit(1000,$this->user->getId());
        $API_debit_json = $this->UserController->debit($this->user,$request,$userService);

        $this->assertJsonStringEqualsJsonString($API_credit_json, serializeToJson($credit_test));
    }

    private function serializeToJson($object){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($object, 'json');
        return $jsonContent;
    }
}