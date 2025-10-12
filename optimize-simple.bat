@echo off
echo 🚀 Optimisation des images FRLimousine...
echo.

REM Créer les dossiers d'images optimisées
if not exist "images\optimized_Mustang_Rouge_PS" mkdir "images\optimized_Mustang_Rouge_PS"
if not exist "images\optimized_Mustang_Bleue_PS" mkdir "images\optimized_Mustang_Bleue_PS"
if not exist "images\optimized_Excalibur_PS" mkdir "images\optimized_Excalibur_PS"
if not exist "images\optimized_Viano_PS" mkdir "images\optimized_Viano_PS"

echo Traitement des images Mustang Rouge...
for %%f in ("images\Mustang Rouge PS\*.jpg") do (
    echo   Optimisation: %%~nxf
    REM Utiliser PowerShell pour redimensionner l'image
    powershell -Command "$img = [System.Drawing.Image]::FromFile('%%f'); $width = $img.Width; $height = $img.Height; if ($width -gt 1200) { $newWidth = 1200; $newHeight = [math]::Floor(($height * 1200) / $width) } else { $newWidth = $width; $newHeight = $height }; $img.Save('images\optimized_Mustang_Rouge_PS\%%~nxf', [System.Drawing.Imaging.ImageFormat]::Jpeg)"
)

echo Traitement des images Mustang Bleu...
for %%f in ("images\Mustang Bleue PS\*.jpg") do (
    echo   Optimisation: %%~nxf
    powershell -Command "$img = [System.Drawing.Image]::FromFile('%%f'); $width = $img.Width; $height = $img.Height; if ($width -gt 1200) { $newWidth = 1200; $newHeight = [math]::Floor(($height * 1200) / $width) } else { $newWidth = $width; $newHeight = $height }; $img.Save('images\optimized_Mustang_Bleue_PS\%%~nxf', [System.Drawing.Imaging.ImageFormat]::Jpeg)"
)

echo Traitement des images Excalibur...
for %%f in ("images\Excalibur PS\*.jpg") do (
    echo   Optimisation: %%~nxf
    powershell -Command "$img = [System.Drawing.Image]::FromFile('%%f'); $width = $img.Width; $height = $img.Height; if ($width -gt 1200) { $newWidth = 1200; $newHeight = [math]::Floor(($height * 1200) / $width) } else { $newWidth = $width; $newHeight = $height }; $img.Save('images\optimized_Excalibur_PS\%%~nxf', [System.Drawing.Imaging.ImageFormat]::Jpeg)"
)

echo Traitement des images Viano...
for %%f in ("images\Viano PS\*.jpg") do (
    echo   Optimisation: %%~nxf
    powershell -Command "$img = [System.Drawing.Image]::FromFile('%%f'); $width = $img.Width; $height = $img.Height; if ($width -gt 1200) { $newWidth = 1200; $newHeight = [math]::Floor(($height * 1200) / $width) } else { $newWidth = $width; $newHeight = $height }; $img.Save('images\optimized_Viano_PS\%%~nxf', [System.Drawing.Imaging.ImageFormat]::Jpeg)"
)

echo.
echo ✅ Optimisation terminee!
echo.
echo 📝 Prochaines etapes:
echo 1. Remplacez les chemins d'images dans galerie.html:
echo    Remplacez 'images/Mustang Rouge PS/' par 'images/optimized_Mustang_Rouge_PS/'
echo    Remplacez 'images/Mustang Bleue PS/' par 'images/optimized_Mustang_Bleue_PS/'
echo    Remplacez 'images/Excalibur PS/' par 'images/optimized_Excalibur_PS/'
echo    Remplacez 'images/Viano PS/' par 'images/optimized_Viano_PS/'
echo.
echo 2. Les images optimisees sont dans les dossiers 'images/optimized_*'
echo 3. Testez le site pour verifier la qualite des images
echo.
pause