<?php
/**
 * receive-pdf.php - Script automatique pour recevoir les devis PDF FRLimousine
 * Optimisé pour hébergement OVH
 */

// Configuration
$uploadDir = 'pdfs/';
$emailNotification = 'proayoubfarkh@gmail.com';
$logFile = 'pdfs/reception.log';

// Créer le répertoire s'il n'existe pas
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Fonction de logging
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Récupérer les données JSON envoyées
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Vérifier que les données sont valides
if (!$data || !isset($data['client'])) {
    writeLog("ERREUR: Données JSON invalides reçues");
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// Nettoyer le nom de fichier
$filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $data['filename']);
$filepath = $uploadDir . $filename;

// Sauvegarder le contenu PDF
if (file_put_contents($filepath, $data['content'])) {
    writeLog("PDF sauvegardé: $filename");
} else {
    writeLog("ERREUR: Impossible de sauvegarder le PDF: $filename");
    http_response_code(500);
    echo json_encode(['error' => 'Erreur sauvegarde PDF']);
    exit;
}

// Sauvegarder les informations client
$infoFile = $uploadDir . str_replace('.html', '_info.json', $filename);
file_put_contents($infoFile, json_encode($data['client'], JSON_PRETTY_PRINT));

// Préparer l'email de notification
$client = $data['client'];
$subject = '🚗 Nouveau devis PDF - ' . $client['nom'];
$message = "Bonjour FRLimousine,

Un nouveau devis a été généré automatiquement sur votre site :

📋 INFORMATIONS CLIENT
Nom: {$client['nom']}
Email: {$client['email']}
Téléphone: {$client['telephone']}

🚗 DÉTAILS DE RÉSERVATION
Service: {$client['service']}
Véhicule: {$client['vehicule']}
Passagers: {$client['passagers']}
Date: {$client['date']}
Durée: {$client['duree']}
Prix: {$client['prix']}

📄 FICHIER PDF
Emplacement: $filepath
Nom du fichier: $filename

⏰ Reçu le: " . date('d/m/Y à H:i:s') . "

Cordialement,
Système automatique FRLimousine";

$headers = 'From: noreply@votre-domaine.ovh' . "\r\n" .
           'Reply-To: ' . $client['email'] . "\r\n" .
           'X-Mailer: PHP/' . phpversion() . "\r\n" .
           'Content-Type: text/plain; charset=UTF-8';

// Envoyer l'email de notification
if (mail($emailNotification, $subject, $message, $headers)) {
    writeLog("Email de notification envoyé pour: " . $client['nom']);
} else {
    writeLog("ATTENTION: Impossible d'envoyer l'email de notification");
}

// Réponse de succès
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'PDF reçu avec succès',
    'filename' => $filename,
    'filepath' => $filepath,
    'server' => $_SERVER['SERVER_NAME'],
    'timestamp' => date('Y-m-d H:i:s')
]);

writeLog("Devis traité avec succès pour: " . $client['nom']);
?>