<?php
// src/Service/OpenAIService.php (ou un autre nom, selon votre service)
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAIService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey ="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")
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * Vérifie si le commentaire est acceptable.
     * Ici, on vérifie simplement la présence de mots interdits.
     */
    public function isCommentAcceptable(string $comment): bool
    {
        // Liste des mots interdits (à adapter selon vos besoins)
        $forbiddenWords = [
            // Profanités en anglais
            'fuck', 'fucking', 'motherfucker', 'shit', 'bitch', 'asshole', 'dick', 'cunt', 'bastard', 'damn',
            'ass', 'asshole', 'assholes', 'asshole',
            'bitch', 'bitches', 'bitch',
            
    
            // Profanités en français
            'putain', 'merde', 'connard', 'connasse', 'enfoiré', 'enfoirée', 'foutre', 'nique', 'nique ta mère', 'salope',
            'enculé', 'enculer', 'fils de pute', 'ta gueule', 'cul', 'bite', 'couille', 'chier', 'bordel', 'abruti', 'abrutie',
            'pd', 'trou du cul', 'chiant', 'chiante', 'va te faire foutre', 
            'fou', 'fouille', 'fouillette', 'fouilleuse', 'fouilleuses', 'fouilleuse', 'fouilleuses',

        ];
        $commentLower = strtolower($comment);

        // Parcourir la liste des mots interdits
        foreach ($forbiddenWords as $word) {
            if (strpos($commentLower, strtolower($word)) !== false) {
                // Si un mot interdit est trouvé, le commentaire n'est pas acceptable
                return false;
            }
        }

        // Si aucun mot interdit n'est trouvé, le commentaire est acceptable
        return true;
    }

    // Vous pouvez ajouter ici d'autres méthodes, par exemple pour des appels à l'API OpenAI
}
