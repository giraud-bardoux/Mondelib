<?php
/**
 * Test rapide du module PhotoFloue
 * À exécuter depuis la racine de SocialEngine
 */

echo "🧪 Test Rapide PhotoFloue Module\n";
echo "=================================\n\n";

$modulePath = 'application/modules/PhotoFloue/';

// Vérifier si le module existe
if (!is_dir($modulePath)) {
    echo "❌ ERREUR: Module PhotoFloue non trouvé dans {$modulePath}\n";
    exit(1);
}

echo "✅ Module PhotoFloue trouvé\n";

// Fichiers essentiels à vérifier
$essentialFiles = array(
    'Bootstrap.php',
    'Plugin/Core.php',
    'View/Helper/ItemBackgroundPhoto.php',
    'externals/styles/photofloue.css',
    'externals/scripts/photofloue.js',
    'settings/manifest.php',
    'settings/install.php'
);

echo "\n📁 Vérification des fichiers essentiels:\n";
$allFilesOk = true;

foreach ($essentialFiles as $file) {
    $fullPath = $modulePath . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        echo "✅ {$file} ({$size} bytes)\n";
    } else {
        echo "❌ {$file} - MANQUANT\n";
        $allFilesOk = false;
    }
}

// Test du CSS
echo "\n🎨 Test du fichier CSS:\n";
$cssPath = $modulePath . 'externals/styles/photofloue.css';
if (file_exists($cssPath)) {
    $cssContent = file_get_contents($cssPath);
    $classes = array('.photofloue-blurred', '.photofloue-protected', 'filter: blur(');
    
    foreach ($classes as $class) {
        if (strpos($cssContent, $class) !== false) {
            echo "✅ Classe trouvée: {$class}\n";
        } else {
            echo "❌ Classe manquante: {$class}\n";
        }
    }
} else {
    echo "❌ Fichier CSS non trouvé\n";
}

// Test du JavaScript
echo "\n⚡ Test du fichier JavaScript:\n";
$jsPath = $modulePath . 'externals/scripts/photofloue.js';
if (file_exists($jsPath)) {
    $jsContent = file_get_contents($jsPath);
    $functions = array('initPhotoFloue', 'PHOTOFLOUE_CONFIG', 'applyProtections');
    
    foreach ($functions as $func) {
        if (strpos($jsContent, $func) !== false) {
            echo "✅ Fonction trouvée: {$func}\n";
        } else {
            echo "❌ Fonction manquante: {$func}\n";
        }
    }
} else {
    echo "❌ Fichier JavaScript non trouvé\n";
}

// Test du manifest
echo "\n📦 Test du manifest:\n";
$manifestPath = $modulePath . 'settings/manifest.php';
if (file_exists($manifestPath)) {
    include $manifestPath;
    if (isset($_manifest['package']['name'])) {
        echo "✅ Nom du module: {$_manifest['package']['name']}\n";
        echo "✅ Version: {$_manifest['package']['version']}\n";
        echo "✅ Path: {$_manifest['package']['path']}\n";
    } else {
        echo "❌ Manifest mal formé\n";
    }
} else {
    echo "❌ Manifest non trouvé\n";
}

// Résumé
echo "\n📊 RÉSUMÉ:\n";
if ($allFilesOk) {
    echo "🎉 TOUS LES FICHIERS ESSENTIELS SONT PRÉSENTS\n";
    echo "✅ Le module peut être installé via Admin Panel\n";
} else {
    echo "⚠️ CERTAINS FICHIERS MANQUENT\n";
    echo "❌ L'installation peut échouer\n";
}

echo "\n📝 INSTRUCTIONS:\n";
echo "1. Aller dans Admin Panel > Packages\n";
echo "2. Chercher 'PhotoFloue Module'\n";
echo "3. Cliquer 'Install' puis 'Enable'\n";
echo "4. Tester en mode visiteur non connecté\n";
?>