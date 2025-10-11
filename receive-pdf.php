<?php
/**
 * receive-pdf.php - Script automatique pour recevoir les devis PDF FRLimousine
 * Sécurisé contre DDoS, injections et attaques - Optimisé pour OVH Cloud
 */

// Inclure le système de sécurité
require_once 'security.php';
$security = initSecurity();

// Configuration OVH Cloud
$uploadDir = 'pdfs/';
$emailNotification = 'contact@votre-domaine.ovh'; // À remplacer par votre email OVH
$logFile = 'pdfs/reception.log';
$domainName = 'votre-domaine.ovh'; // À remplacer par votre nom de domaine OVH

// Créer le répertoire s'il n'existe pas avec gestion d'erreurs OVH
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        writeLog("ERREUR: Impossible de créer le répertoire $uploadDir");
        http_response_code(500);
        echo json_encode(['error' => 'Erreur création répertoire']);
        exit;
    }
    writeLog("Répertoire $uploadDir créé avec succès");
}

// Fonction de logging
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Vérifications de sécurité avancées
$ip = $_SERVER['REMOTE_ADDR'];

// Vérifier le rate limiting
if (!$security->checkRateLimit($ip)) {
    http_response_code(429);
    echo json_encode(['error' => 'Trop de requêtes - Veuillez réessayer plus tard']);
    exit;
}

// Détecter les bots malveillants
if ($security->detectBot()) {
    $security->logSecurityEvent("BOT_DETECTE", $ip, "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

// Récupérer les données JSON envoyées avec validation de taille
$input = file_get_contents('php://input');
if (strlen($input) > 1048576) { // 1MB max
    writeLog("BLOCAGE: Payload trop volumineux");
    http_response_code(413);
    echo json_encode(['error' => 'Données trop volumineuses']);
    exit;
}

$data = json_decode($input, true);

// Validation stricte des données
if (!$data || !isset($data['client']) || !is_array($data['client'])) {
    writeLog("ERREUR: Structure JSON invalide reçue de $ip");
    http_response_code(400);
    echo json_encode(['error' => 'Structure des données invalide']);
    exit;
}

// Validation des champs obligatoires avec sécurité renforcée
$requiredFields = ['nom', 'email', 'telephone', 'service', 'vehicule', 'passagers', 'date', 'duree', 'prix'];
foreach ($requiredFields as $field) {
    if (!isset($data['client'][$field]) || empty(trim($data['client'][$field]))) {
        $security->logSecurityEvent("CHAMP_MANQUANT", $ip, "Champ: $field");
        writeLog("ERREUR: Champ obligatoire manquant: $field");
        http_response_code(400);
        echo json_encode(['error' => "Champ obligatoire manquant: $field"]);
        exit;
    }
}

// Validation et nettoyage des données avec fonctions de sécurité
$data['client']['nom'] = $security->sanitizeInput($data['client']['nom']);
$data['client']['email'] = $security->sanitizeInput($data['client']['email']);
$data['client']['telephone'] = $security->sanitizeInput($data['client']['telephone']);
$data['client']['service'] = $security->sanitizeInput($data['client']['service']);
$data['client']['vehicule'] = $security->sanitizeInput($data['client']['vehicule']);

// Validation email avancée
if (!$security->validateEmail($data['client']['email'])) {
    $security->logSecurityEvent("EMAIL_INVALIDE", $ip, "Email: " . $data['client']['email']);
    writeLog("ERREUR: Format email invalide: " . $data['client']['email']);
    http_response_code(400);
    echo json_encode(['error' => 'Format email invalide']);
    exit;
}

// Validation du numéro de téléphone (format français)
$cleanPhone = preg_replace('/[^0-9+\-\s()]/', '', $data['client']['telephone']);
if (!preg_match('/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/', $cleanPhone)) {
    $security->logSecurityEvent("TELEPHONE_INVALIDE", $ip, "Tel: " . $data['client']['telephone']);
    writeLog("ERREUR: Format téléphone invalide: " . $data['client']['telephone']);
    http_response_code(400);
    echo json_encode(['error' => 'Format téléphone invalide']);
    exit;
}
$data['client']['telephone'] = $cleanPhone;

// Validation des autres champs pour détecter les attaques
foreach ($data['client'] as $key => $value) {
    if ($security->detectAttack($value)) {
        $security->logSecurityEvent("ATTAQUE_DETECTEE", $ip, "Champ: $key, Valeur: " . substr($value, 0, 100));
        writeLog("BLOCAGE: Tentative d'attaque détectée dans le champ: $key");
        http_response_code(403);
        echo json_encode(['error' => 'Données suspectes détectées']);
        exit;
    }
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

// Protection CSRF - Vérification du token
$expectedToken = hash('sha256', $ip . $_SERVER['HTTP_USER_AGENT'] . date('Y-m-d-H'));
$receivedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (empty($receivedToken) || !hash_equals($expectedToken, $receivedToken)) {
    writeLog("BLOCAGE: Token CSRF invalide pour IP $ip");
    http_response_code(403);
    echo json_encode(['error' => 'Token de sécurité invalide']);
    exit;
}

// Préparer l'email de notification
$client = $data['client'];
$subject = '🚗 Nouveau devis PDF - ' . htmlspecialchars($client['nom'], ENT_QUOTES, 'UTF-8');
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

$headers = 'From: noreply@' . $domainName . "\r\n" .
           'Reply-To: ' . $client['email'] . "\r\n" .
           'X-Mailer: PHP/' . phpversion() . "\r\n" .
           'Content-Type: text/plain; charset=UTF-8' . "\r\n" .
           'Return-Path: noreply@' . $domainName;

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