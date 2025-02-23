<?php
// src/Service/OpenAIService.php (ou un autre nom, selon votre service)
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAIService
{

    public function isCommentAcceptable(string $comment): bool
{
    // Chemin absolu ou relatif vers le fichier ignoré
    $filePath = __DIR__ . '/../forbidden_words.json';
    $jsonContent = file_get_contents($filePath);
    $forbiddenWords = json_decode($jsonContent, true);

    // Convertir le commentaire en minuscules
    $commentLower = strtolower($comment);

    // Vérifier la présence de mots interdits
    foreach ($forbiddenWords as $word) {
        if (strpos($commentLower, strtolower($word)) !== false) {
            return false;
        }
    }

    return true;
}


    // Vous pouvez ajouter ici d'autres méthodes, par exemple pour des appels à l'API OpenAI
}
