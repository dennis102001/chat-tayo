<?php

namespace App\Services;

use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;

class BrevoMailService
{
    private Brevo $client;

    public function __construct()
    {
        $this->client = new Brevo(
            config('services.brevo.api_key'),
            [
                'timeout' => 30,
            ]
        );
    }

    public function send(string $email, string $subject, string $htmlContent)
    {
        return $this->client->transactionalEmails->sendTransacEmail(
            new SendTransacEmailRequest([
                'subject' => $subject,
                'htmlContent' => $htmlContent,
                'sender' => new SendTransacEmailRequestSender([
                    'name' => config('mail.from.name'),
                    'email' => config('mail.from.address'),
                ]),
                'to' => [
                    new SendTransacEmailRequestToItem([
                        'email' => $email,
                    ]),
                ],
            ])
        );
    }
}