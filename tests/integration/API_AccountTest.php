<?php

namespace App\Tests\integration;

use App\Entity\User;
use App\Controller\UserController;
use App\Service\CreditOperationService;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\EmailSenderService;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class API_AccountTest extends KernelTestCase
{
    private User $user;
    private UserController $userController;
    private $emailSenderService;
    private $userService;
    private $creditOperationService;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->user = new User();

        $container = static::getContainer();

        $this->userService = $container->get(UserService::class);
        $this->creditOperationService = $container->get(CreditOperationService::class);

        $this->user->setEmail('unit@test.esgi');
        $this->user->setFirstName('first_name');
        $this->user->setLastName('last_name');
        $this->user->setBankAccount(900);
        $this->emailSenderService = $this->getMockBuilder(EmailSenderService::class)
            ->onlyMethods(['sendEmail'])
            ->getMock();
        $this->userController = new UserController();
        parent::setUp();
    }

    public function testGet_account(): void
    {
        $user_test = $this->user;

        $API_user_json = $this->UserController->getBankAccount($this->user, $this->userService);

        $this->assertJsonStringEqualsJsonString($API_user_json, serializeToJson($user_test));
    }

    public function testPut_credit(): void
    {
        $request = new Request(["amount" => "1000"]);
        $credit_test = $this->userService->credit(1000, $this->user);
        $API_credit_json = $this->UserController->credit($this->user, $request, $this->userService);

        $this->assertJsonStringEqualsJsonString($API_credit_json, serializeToJson($credit_test));
    }

    public function testPut_debit(): void
    {
        $request = new Request(["amount" => "1000"]);
        $debit_test = $this->userService->debit(1000, $this->user);
        $API_debit_json = $this->UserController->debit($this->user, $request, $this->userService);

        $this->assertJsonStringEqualsJsonString($API_credit_json, serializeToJson($credit_test));
    }

    private function serializeToJson($object)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($object, 'json');
        return $jsonContent;
    }
}
