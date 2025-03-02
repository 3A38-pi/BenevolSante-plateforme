<?php
namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $twilioClient;
    private $twilioPhoneNumber;

    public function __construct(string $twilioSid, string $twilioAuthToken, string $twilioPhoneNumber)
    {
        $this->twilioClient = new Client($twilioSid, $twilioAuthToken);
        $this->twilioPhoneNumber = $twilioPhoneNumber;
    }

    public function sendSms(string $to, string $message): void
    {
        $this->twilioClient->messages->create(
            $to,
            [
                'from' => $this->twilioPhoneNumber,
                'body' => $message
            ]
        );
    }
}
