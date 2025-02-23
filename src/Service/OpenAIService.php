<?php
// src/Service/OpenAIService.php (ou un autre nom, selon votre service)
namespace App\Service;

class OpenAIService
{

    public function isCommentAcceptable(string $comment): bool
{
    $filePath = __DIR__ . '/../forbidden_words.json';
    $jsonContent = file_get_contents($filePath);
    $forbiddenWords = json_decode($jsonContent, true);


    $commentLower = strtolower($comment);

    foreach ($forbiddenWords as $word) {
        if (strpos($commentLower, strtolower($word)) !== false) {
            return false;
        }
    }

    return true;
}

}
