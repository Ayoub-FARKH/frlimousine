# Script d'optimisation des images FRLimousine avec PowerShell
# Utilise les outils système disponibles pour compresser les images

param(
    [int]$MaxWidth = 1200,
    [int]$MaxHeight = 800,
    [int]$Quality = 85
)

Write-Host "🚀 Démarrage de l'optimisation des images FRLimousine..." -ForegroundColor Green

# Dossiers à traiter
$sourceDirs = @(
    "images/Mustang Rouge PS",
    "images/Mustang Bleue PS",
    "images/Excalibur PS",
    "images/Viano PS"
)

$totalOriginalSize = 0
$totalOptimizedSize = 0
$processedCount = 0

foreach ($dir in $sourceDirs) {
    if (!(Test-Path $dir)) {
        Write-Host "Dossier introuvable: $dir" -ForegroundColor Red
        continue
    }

    Write-Host "Traitement du dossier: $dir" -ForegroundColor Cyan

    # Créer le dossier d'images optimisées
    $optimizedDir = "images/optimized_" + (Split-Path $dir -Leaf)

    if (!(Test-Path $optimizedDir)) {
        New-Item -ItemType Directory -Path $optimizedDir | Out-Null
    }

    $files = Get-ChildItem -Path $dir -Name -Include "*.jpg", "*.jpeg"

    foreach ($file in $files) {
        $sourcePath = Join-Path $dir $file
        $destinationPath = Join-Path $optimizedDir $file

        $originalSize = (Get-Item $sourcePath).Length

        Write-Host "  Optimisation: $file ($([math]::Round($originalSize/1MB, 2)) MB)" -NoNewline

        try {
            # Utiliser l'outil de redimensionnement de Windows
            Add-Type -AssemblyName System.Drawing

            $img = [System.Drawing.Image]::FromFile($sourcePath)
            $width = $img.Width
            $height = $img.Height

            # Calculer les nouvelles dimensions
            $newWidth = $width
            $newHeight = $height

            if ($width -gt $MaxWidth) {
                $newWidth = $MaxWidth
                $newHeight = [math]::Floor(($height * $MaxWidth) / $width)
            }

            if ($newHeight -gt $MaxHeight) {
                $newWidth = [math]::Floor(($newWidth * $MaxHeight) / $newHeight)
                $newHeight = $MaxHeight
            }

            # Créer la nouvelle image
            $newImg = New-Object System.Drawing.Bitmap $newWidth, $newHeight
            $graphics = [System.Drawing.Graphics]::FromImage($newImg)
            $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
            $graphics.DrawImage($img, 0, 0, $newWidth, $newHeight)

            # Encoder avec qualité réduite
            $encoderParams = New-Object System.Drawing.Imaging.EncoderParameters 1
            $encoderParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter ([System.Drawing.Imaging.Encoder]::Quality, $Quality)

            $jpegCodec = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object {$_.FormatDescription -eq "JPEG"}
            $newImg.Save($destinationPath, $jpegCodec, $encoderParams)

            $optimizedSize = (Get-Item $destinationPath).Length
            $savings = $originalSize - $optimizedSize
            $savingsPercent = [math]::Round(($savings / $originalSize) * 100, 1)

            Write-Host " → $([math]::Round($optimizedSize/1MB, 2)) MB (économisé: $($savingsPercent)%)" -ForegroundColor Green

            $totalOriginalSize += $originalSize
            $totalOptimizedSize += $optimizedSize
            $processedCount++

            # Libérer la mémoire
            $img.Dispose()
            $newImg.Dispose()
            $graphics.Dispose()

        } catch {
            Write-Host " → ÉCHEC ($_)" -ForegroundColor Red
        }
    }
    Write-Host ""
}

# Résultats finaux
$totalSavings = $totalOriginalSize - $totalOptimizedSize
$totalSavingsPercent = [math]::Round(($totalSavings / $totalOriginalSize) * 100, 1)

Write-Host "📊 RÉSUMÉ DE L'OPTIMISATION:" -ForegroundColor Yellow
Write-Host "Images traitées: $processedCount" -ForegroundColor White
Write-Host "Taille originale totale: $([math]::Round($totalOriginalSize/1MB, 2)) MB" -ForegroundColor White
Write-Host "Taille optimisée totale: $([math]::Round($totalOptimizedSize/1MB, 2)) MB" -ForegroundColor White
Write-Host "Espace économisé: $([math]::Round($totalSavings/1MB, 2)) MB ($($totalSavingsPercent)%)" -ForegroundColor Green

Write-Host ""
Write-Host "✅ Optimisation terminée!" -ForegroundColor Green

# Instructions pour remplacer les images dans le HTML
Write-Host ""
Write-Host "📝 PROCHAINES ÉTAPES:" -ForegroundColor Yellow
Write-Host "1. Remplacez les chemins d'images dans galerie.html:" -ForegroundColor White
foreach ($dir in $sourceDirs) {
    $optimizedDir = "images/optimized_" + (Split-Path $dir -Leaf)
    Write-Host "   Remplacez '$dir/' par '$optimizedDir/'" -ForegroundColor Gray
}
Write-Host ""
Write-Host "2. Les images optimisées sont dans les dossiers 'images/optimized_*'" -ForegroundColor White
Write-Host "3. Test the site to verify image quality" -ForegroundColor White