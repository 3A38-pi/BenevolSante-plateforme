<?php
require_once './vendor/autoload.php';

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

// Create a Transport object
$transport = Transport::fromDsn('smtp://amroush123@gmail.com:npcfowmbtolgyqfe@smtp.gmail.com:587');

// Create a Mailer object
$mailer = new Mailer($transport);

// Create an Email object
$email = (new Email());

// Set the "From" address
$email->from('amroush123@gmail.com');

// Set the "To" address
$email->to(
    'deganiomar69@gmail.com'
);
// Set a "subject"
$email->subject('A Cool Subject!');

// Set the plain-text "body"
$email->text('The plain text version of the message.');

// Send email
$mailer->send($email);