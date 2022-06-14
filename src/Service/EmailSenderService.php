<?php

namespace App\Entity;

use Exception;

class EmailSenderService
{

    public function sendEmail(string $email, string $message)
    {
        echo $message;
    }

}