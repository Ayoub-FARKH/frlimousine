<?php
/**
 * optimize-images.php - Script d'optimisation automatique des images FRLimousine
 * Compresser et convertir les images pour de meilleures performances
 */

// Configuration
$sourceDirs = ['images/2022/', 'images/webp/'];
$backupDir = 'images/backup/';
$quality = 85; // Qualité JPEG/WebP
$maxWidth = 1200; // Largeur maximale
$maxHeight = 800; // Hauteur maximale

// Créer le répertoire de sauvegarde
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Fonction de sauvegarde
function backupImage($source) {
    global $backupDir;
    $backupPath = $backupDir . basename($source);
    return copy($source, $backupPath);
}

// Fonction de redimensionnement d'image
function resizeImage($source, $destination, $maxWidth, $maxHeight, $quality) {
    list($width, $height, $type) = getimagesize($source);

    // Calculer les nouvelles dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);

    // Créer l'image de destination
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Gérer la transparence pour PNG
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }

    // Charger l'image source
    switch ($type) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($source);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    // Redimensionner
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Sauvegarder selon le type
    $extension = pathinfo($destination, PATHINFO_EXTENSION);
    switch (strtolower($extension)) {
        case 'jpg':
        case 'jpeg':
            return imagejpeg($newImage, $destination, $quality);
        case 'png':
            return imagepng($newImage, $destination, 9);
        case 'webp':
            return imagewebp($newImage, $destination, $quality);
        default:
            return false;
    }

    // Nettoyer la mémoire
    imagedestroy($newImage);
    imagedestroy($sourceImage);
}

// Fonction d'optimisation principale
function optimizeImages($dirs, $quality, $maxWidth, $maxHeight) {
    $stats = [
        'processed' => 0,
        'saved_bytes' => 0,
        'errors' => 0
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            echo "Répertoire $dir non trouvé\n";
            continue;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $sourcePath = $dir . $file;
            if (!is_file($sourcePath)) continue;

            // Vérifier l'extension
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            // Sauvegarder l'original
            if (!backupImage($sourcePath)) {
                echo "Erreur sauvegarde: $sourcePath\n";
                $stats['errors']++;
                continue;
            }

            // Taille originale
            $originalSize = filesize($sourcePath);

            // Optimiser l'image
            if (resizeImage($sourcePath, $sourcePath, $maxWidth, $maxHeight, $quality)) {
                $newSize = filesize($sourcePath);
                $savedBytes = $originalSize - $newSize;

                $stats['processed']++;
                $stats['saved_bytes'] += $savedBytes;

                echo "✅ Optimisé: $file | Économisé: " . round($savedBytes / 1024, 1) . " KB\n";
            } else {
                echo "❌ Erreur optimisation: $file\n";
                $stats['errors']++;
            }
        }
    }

    return $stats;
}

// Fonction de génération WebP
function generateWebP($sourceDir) {
    $stats = ['generated' => 0, 'errors' => 0];

    if (!is_dir($sourceDir)) return $stats;

    $files = scandir($sourceDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $sourcePath = $sourceDir . $file;
        if (!is_file($sourcePath)) continue;

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) continue;

        $webpPath = $sourceDir . pathinfo($file, PATHINFO_FILENAME) . '.webp';

        // Vérifier si WebP existe déjà
        if (file_exists($webpPath)) continue;

        // Générer WebP
        if (generateWebPFromImage($sourcePath, $webpPath)) {
            $stats['generated']++;
            echo "🆕 WebP généré: " . basename($webpPath) . "\n";
        } else {
            $stats['errors']++;
            echo "❌ Erreur WebP: " . basename($sourcePath) . "\n";
        }
    }

    return $stats;
}

function generateWebPFromImage($source, $destination) {
    list($width, $height, $type) = getimagesize($source);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        default:
            return false;
    }

    // Sauvegarder en WebP avec qualité optimisée
    $result = imagewebp($image, $destination, 85);

    imagedestroy($image);
    return $result;
}

// Exécution de l'optimisation
echo "🚗 Optimisation des images FRLimousine\n";
echo "========================================\n\n";

// Optimisation des images existantes
echo "📷 Étape 1: Redimensionnement et compression...\n";
$optimizationStats = optimizeImages($sourceDirs, $quality, $maxWidth, $maxHeight);

// Génération des WebP
echo "\n🌐 Étape 2: Génération des images WebP...\n";
$webpStats = generateWebP('images/2022/');

// Résumé
echo "\n📊 RAPPORT D'OPTIMISATION\n";
echo "========================\n";
echo "Images traitées: " . $optimizationStats['processed'] . "\n";
echo "Images WebP générées: " . $webpStats['generated'] . "\n";
echo "Espace économisé: " . round($optimizationStats['saved_bytes'] / 1024, 1) . " KB\n";
echo "Erreurs: " . ($optimizationStats['errors'] + $webpStats['errors']) . "\n";

$totalSavings = round($optimizationStats['saved_bytes'] / 1024, 1);
if ($totalSavings > 0) {
    echo "\n✅ Optimisation réussie ! Économies réalisées: ${totalSavings} KB\n";
    echo "💡 Votre site se chargera plus rapidement avec ces images optimisées.\n";
} else {
    echo "\n⚠️ Aucune économie réalisée. Vérifiez les paramètres d'optimisation.\n";
}

// Conseils finaux
echo "\n🔧 PROCHAINES ÉTAPES RECOMMANDÉES:\n";
echo "1. Téléversez les images optimisées sur votre serveur OVH\n";
echo "2. Utilisez le fichier .htaccess-optimisation pour activer la compression\n";
echo "3. Testez les performances avec Google PageSpeed Insights\n";
echo "4. Activez le lazy loading dans votre HTML\n";
echo "5. Configurez un CDN pour des performances optimales\n";

echo "\n🎯 Objectif atteint: Site plus rapide et mieux référencé !\n";
?>