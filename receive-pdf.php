<?php
// receive-pdf.php - Script pour recevoir automatiquement les devis PDF

// Récupérer les données JSON envoyées
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Vérifier que les données sont valides
if (!$data || !isset($data['client'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// Créer le répertoire pdfs s'il n'existe pas
if (!file_exists('pdfs')) {
    mkdir('pdfs', 0777, true);
}

// Nom du fichier
$filename = $data['filename'];
$filepath = 'pdfs/' . $filename;

// Sauvegarder le contenu PDF
file_put_contents($filepath, $data['content']);

// Sauvegarder les informations client
$infoFile = 'pdfs/' . str_replace('.html', '_info.json', $filename);
file_put_contents($infoFile, json_encode($data['client'], JSON_PRETTY_PRINT));

// Email de notification (optionnel)
$to = 'proayoubfarkh@gmail.com';
$subject = 'Nouveau devis PDF - ' . $data['client']['nom'];
$message = "Un nouveau devis a été généré automatiquement.\n\n" .
           "Client: " . $data['client']['nom'] . "\n" .
           "Email: " . $data['client']['email'] . "\n" .
           "Téléphone: " . $data['client']['telephone'] . "\n" .
           "Véhicule: " . $data['client']['vehicule'] . "\n" .
           "Prix: " . $data['client']['prix'] . "\n\n" .
           "Fichier PDF: " . $filepath;

$headers = 'From: noreply@frlimousine.com' . "\r\n" .
           'Reply-To: ' . $data['client']['email'] . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);

// Réponse de succès
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'PDF reçu avec succès',
    'filename' => $filename,
    'filepath' => $filepath
]);
?>