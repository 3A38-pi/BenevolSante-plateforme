<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class PdfGeneratorService
{

    public function generatePdf(string $html): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        // Load HTML content
        $dompdf->loadHtml($html);

        // Render the PDF
        $dompdf->render();

        // Save the PDF to a temporary file
        $output = $dompdf->output();
        $pdfPath = sys_get_temp_dir() . '/recu_don.pdf'; // Chemin temporaire pour le fichier PDF
        file_put_contents($pdfPath, $output);

        return $pdfPath; // Retourne le chemin du fichier PDF généré
    }


    
}

