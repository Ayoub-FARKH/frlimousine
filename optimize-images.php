<?php
/**
 * Script d'optimisation des images FRLimousine
 * Compression et redimensionnement des images pour améliorer les performances
 */

// Configuration
$sourceDirs = [
    'images/Mustang Rouge PS',
    'images/Mustang Bleue PS',
    'images/Excalibur PS',
    'images/Viano PS'
];

$maxWidth = 1200;  // Largeur maximale
$maxHeight = 800;  // Hauteur maximale
$quality = 85;     // Qualité JPEG (1-100)

// Fonction pour optimiser une image
function optimizeImage($sourcePath, $destinationPath, $maxWidth, $maxHeight, $quality) {
    // Vérifier si l'image source existe
    if (!file_exists($sourcePath)) {
        echo "Erreur: Fichier source introuvable: $sourcePath\n";
        return false;
    }

    // Créer le dossier de destination si nécessaire
    $destinationDir = dirname($destinationPath);
    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    // Obtenir les informations de l'image
    list($width, $height, $type) = getimagesize($sourcePath);

    // Calculer les nouvelles dimensions
    $newWidth = $width;
    $newHeight = $height;

    if ($width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = ($height * $maxWidth) / $width;
    }

    if ($newHeight > $maxHeight) {
        $newWidth = ($newWidth * $maxHeight) / $newHeight;
        $newHeight = $maxHeight;
    }

    // Créer l'image optimisée
    switch ($type) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        default:
            echo "Type d'image non supporté: $type\n";
            return false;
    }

    if (!$sourceImage) {
        echo "Erreur lors de la création de l'image: $sourcePath\n";
        return false;
    }

    // Créer la nouvelle image
    $optimizedImage = imagecreatetruecolor($newWidth, $newHeight);

    // Conserver la transparence pour les PNG
    if ($type === IMAGETYPE_PNG) {
        imagealphablending($optimizedImage, false);
        imagesavealpha($optimizedImage, true);
    }

    // Redimensionner
    imagecopyresampled($optimizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Sauvegarder l'image optimisée
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($optimizedImage, $destinationPath, $quality);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($optimizedImage, $destinationPath, 9);
            break;
    }

    // Libérer la mémoire
    imagedestroy($sourceImage);
    imagedestroy($optimizedImage);

    return $success;
}

// Fonction pour obtenir la taille d'un dossier
function getDirectorySize($path) {
    $totalSize = 0;
    $files = scandir($path);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $filePath = $path . '/' . $file;
        if (is_file($filePath)) {
            $totalSize += filesize($filePath);
        } elseif (is_dir($filePath)) {
            $totalSize += getDirectorySize($filePath);
        }
    }

    return $totalSize;
}

// Script principal
echo "🚀 Démarrage de l'optimisation des images FRLimousine...\n\n";

$totalOriginalSize = 0;
$totalOptimizedSize = 0;
$processedCount = 0;

foreach ($sourceDirs as $dir) {
    if (!is_dir($dir)) {
        echo "Dossier introuvable: $dir\n";
        continue;
    }

    echo "Traitement du dossier: $dir\n";

    // Créer le dossier d'images optimisées
    $optimizedDir = 'images/optimized_' . basename($dir);

    if (!is_dir($optimizedDir)) {
        mkdir($optimizedDir, 0755, true);
    }

    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $sourcePath = $dir . '/' . $file;

        if (is_file($sourcePath) && preg_match('/\.(jpg|jpeg|png)$/i', $file)) {
            $destinationPath = $optimizedDir . '/' . $file;

            $originalSize = filesize($sourcePath);

            echo "  Optimisation: $file (" . round($originalSize / 1024 / 1024, 2) . " MB)";

            if (optimizeImage($sourcePath, $destinationPath, $maxWidth, $maxHeight, $quality)) {
                $optimizedSize = filesize($destinationPath);
                $savings = $originalSize - $optimizedSize;
                $savingsPercent = round(($savings / $originalSize) * 100, 1);

                echo " → " . round($optimizedSize / 1024 / 1024, 2) . " MB (économisé: {$savingsPercent}%)\n";

                $totalOriginalSize += $originalSize;
                $totalOptimizedSize += $optimizedSize;
                $processedCount++;
            } else {
                echo " → ÉCHEC\n";
            }
        }
    }
    echo "\n";
}

// Résultats finaux
$totalSavings = $totalOriginalSize - $totalOptimizedSize;
$totalSavingsPercent = round(($totalSavings / $totalOriginalSize) * 100, 1);

echo "📊 RÉSUMÉ DE L'OPTIMISATION:\n";
echo "Images traitées: $processedCount\n";
echo "Taille originale totale: " . round($totalOriginalSize / 1024 / 1024, 2) . " MB\n";
echo "Taille optimisée totale: " . round($totalOptimizedSize / 1024 / 1024, 2) . " MB\n";
echo "Espace économisé: " . round($totalSavings / 1024 / 1024, 2) . " MB ({$totalSavingsPercent}%)\n";
echo "\n✅ Optimisation terminée!\n";

// Instructions pour remplacer les images dans le HTML
echo "\n📝 PROCHAINES ÉTAPES:\n";
echo "1. Remplacez les chemins d'images dans galerie.html:\n";
foreach ($sourceDirs as $dir) {
    $optimizedDir = 'images/optimized_' . basename($dir);
    echo "   Remplacez '$dir/' par '$optimizedDir/'\n";
}
echo "\n2. Les images optimisées sont dans les dossiers 'images/optimized_*'\n";
echo "3. Testez le site pour vérifier la qualité des images\n";
?>