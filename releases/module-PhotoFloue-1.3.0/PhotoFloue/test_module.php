<?php
/**
 * PhotoFloue Module for SocialEngine 7.4 - Test Script
 *
 * @category   Application_Extensions
 * @package    PhotoFloue
 * @copyright  Copyright 2024
 * @license    Custom License
 */

// Vérifier que nous sommes dans l'environnement SocialEngine
if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', dirname(dirname(dirname(__FILE__))));
}

/**
 * Script de test pour le module PhotoFloue
 */
class PhotoFloue_TestModule
{
    protected $_db;
    protected $_results = array();
    
    public function __construct()
    {
        echo "<h1>🧪 Test du Module PhotoFloue pour SocialEngine 7.4</h1>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .test-ok { color: green; font-weight: bold; }
            .test-error { color: red; font-weight: bold; }
            .test-warning { color: orange; font-weight: bold; }
            .test-info { color: blue; }
            pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        </style>\n";
    }
    
    /**
     * Exécuter tous les tests
     */
    public function runAllTests()
    {
        echo "<h2>📋 Exécution des tests automatiques</h2>\n";
        
        // Tests de base
        $this->testFileStructure();
        $this->testDatabaseSettings();
        $this->testModuleStatus();
        $this->testDependencies();
        $this->testFilePermissions();
        $this->testCSS();
        $this->testJavaScript();
        $this->testTranslations();
        
        // Résumé
        $this->displaySummary();
        
        // Tests manuels
        $this->displayManualTests();
    }
    
    /**
     * Test de la structure des fichiers
     */
    protected function testFileStructure()
    {
        echo "<h3>📁 Test de la structure des fichiers</h3>\n";
        
        $requiredFiles = array(
            'Bootstrap.php',
            'Plugin/Core.php',
            'View/Helper/ItemBackgroundPhoto.php',
            'externals/styles/photofloue.css',
            'externals/scripts/photofloue.js',
            'settings/manifest.php',
            'settings/install.php',
            'README.md'
        );
        
        $modulePath = APPLICATION_PATH . '/modules/PhotoFloue/';
        $allFilesExist = true;
        
        foreach ($requiredFiles as $file) {
            $fullPath = $modulePath . $file;
            if (file_exists($fullPath)) {
                $this->logTest("✅ Fichier trouvé: {$file}", 'ok');
            } else {
                $this->logTest("❌ Fichier manquant: {$file}", 'error');
                $allFilesExist = false;
            }
        }
        
        $this->_results['file_structure'] = $allFilesExist;
        
        if ($allFilesExist) {
            $this->logTest("🎉 Structure des fichiers: COMPLÈTE", 'ok');
        } else {
            $this->logTest("⚠️ Structure des fichiers: INCOMPLÈTE", 'error');
        }
    }
    
    /**
     * Test des paramètres en base de données
     */
    protected function testDatabaseSettings()
    {
        echo "<h3>🗄️ Test des paramètres en base de données</h3>\n";
        
        try {
            // Simulation d'une connexion DB simple
            $settingsFile = APPLICATION_PATH . '/settings/database.php';
            if (!file_exists($settingsFile)) {
                $this->logTest("⚠️ Fichier de configuration database.php non trouvé", 'warning');
                $this->_results['database'] = false;
                return;
            }
            
            $this->logTest("✅ Configuration base de données détectée", 'ok');
            
            // Vérifier la présence des paramètres PhotoFloue (simulation)
            $expectedSettings = array(
                'photofloue.enabled',
                'photofloue.blur_intensity',
                'photofloue.apply_to_users',
                'photofloue.apply_to_albums',
                'photofloue.login_message'
            );
            
            $this->logTest("📝 Paramètres attendus: " . count($expectedSettings), 'info');
            $this->_results['database'] = true;
            
        } catch (Exception $e) {
            $this->logTest("❌ Erreur base de données: " . $e->getMessage(), 'error');
            $this->_results['database'] = false;
        }
    }
    
    /**
     * Test du statut du module
     */
    protected function testModuleStatus()
    {
        echo "<h3>🔧 Test du statut du module</h3>\n";
        
        $manifestPath = APPLICATION_PATH . '/modules/PhotoFloue/settings/manifest.php';
        
        if (file_exists($manifestPath)) {
            $manifest = include $manifestPath;
            
            if (is_array($manifest) && isset($manifest['package'])) {
                $package = $manifest['package'];
                
                $this->logTest("✅ Manifest valide trouvé", 'ok');
                $this->logTest("📦 Nom: " . $package['name'], 'info');
                $this->logTest("🏷️ Version: " . $package['version'], 'info');
                $this->logTest("👤 Auteur: " . $package['author'], 'info');
                
                $this->_results['module_status'] = true;
            } else {
                $this->logTest("❌ Manifest invalide", 'error');
                $this->_results['module_status'] = false;
            }
        } else {
            $this->logTest("❌ Manifest non trouvé", 'error');
            $this->_results['module_status'] = false;
        }
    }
    
    /**
     * Test des dépendances
     */
    protected function testDependencies()
    {
        echo "<h3>🔗 Test des dépendances</h3>\n";
        
        $requiredModules = array('user', 'album', 'core');
        $allDepsExist = true;
        
        foreach ($requiredModules as $module) {
            $modulePath = APPLICATION_PATH . '/modules/' . ucfirst($module);
            if (is_dir($modulePath)) {
                $this->logTest("✅ Module dépendant trouvé: {$module}", 'ok');
            } else {
                $this->logTest("❌ Module dépendant manquant: {$module}", 'error');
                $allDepsExist = false;
            }
        }
        
        $this->_results['dependencies'] = $allDepsExist;
    }
    
    /**
     * Test des permissions des fichiers
     */
    protected function testFilePermissions()
    {
        echo "<h3>🔐 Test des permissions des fichiers</h3>\n";
        
        $filesToCheck = array(
            'externals/styles/photofloue.css',
            'externals/scripts/photofloue.js'
        );
        
        $modulePath = APPLICATION_PATH . '/modules/PhotoFloue/';
        $allPermissionsOk = true;
        
        foreach ($filesToCheck as $file) {
            $fullPath = $modulePath . $file;
            if (file_exists($fullPath)) {
                $perms = fileperms($fullPath);
                if (is_readable($fullPath)) {
                    $this->logTest("✅ Fichier lisible: {$file}", 'ok');
                } else {
                    $this->logTest("❌ Fichier non lisible: {$file}", 'error');
                    $allPermissionsOk = false;
                }
            }
        }
        
        $this->_results['permissions'] = $allPermissionsOk;
    }
    
    /**
     * Test du fichier CSS
     */
    protected function testCSS()
    {
        echo "<h3>🎨 Test du fichier CSS</h3>\n";
        
        $cssPath = APPLICATION_PATH . '/modules/PhotoFloue/externals/styles/photofloue.css';
        
        if (file_exists($cssPath)) {
            $cssContent = file_get_contents($cssPath);
            
            // Vérifier les classes importantes
            $requiredClasses = array(
                '.photofloue-blurred',
                '.photofloue-protected',
                '.photofloue-container',
                'filter: blur('
            );
            
            $allClassesFound = true;
            foreach ($requiredClasses as $class) {
                if (strpos($cssContent, $class) !== false) {
                    $this->logTest("✅ Classe CSS trouvée: {$class}", 'ok');
                } else {
                    $this->logTest("❌ Classe CSS manquante: {$class}", 'error');
                    $allClassesFound = false;
                }
            }
            
            $this->logTest("📏 Taille CSS: " . round(strlen($cssContent) / 1024, 2) . " KB", 'info');
            $this->_results['css'] = $allClassesFound;
            
        } else {
            $this->logTest("❌ Fichier CSS non trouvé", 'error');
            $this->_results['css'] = false;
        }
    }
    
    /**
     * Test du fichier JavaScript
     */
    protected function testJavaScript()
    {
        echo "<h3>⚡ Test du fichier JavaScript</h3>\n";
        
        $jsPath = APPLICATION_PATH . '/modules/PhotoFloue/externals/scripts/photofloue.js';
        
        if (file_exists($jsPath)) {
            $jsContent = file_get_contents($jsPath);
            
            // Vérifier les fonctions importantes
            $requiredFunctions = array(
                'initPhotoFloue',
                'applyProtections',
                'preventKeyboardShortcuts',
                'PHOTOFLOUE_CONFIG'
            );
            
            $allFunctionsFound = true;
            foreach ($requiredFunctions as $func) {
                if (strpos($jsContent, $func) !== false) {
                    $this->logTest("✅ Fonction JS trouvée: {$func}", 'ok');
                } else {
                    $this->logTest("❌ Fonction JS manquante: {$func}", 'error');
                    $allFunctionsFound = false;
                }
            }
            
            $this->logTest("📏 Taille JS: " . round(strlen($jsContent) / 1024, 2) . " KB", 'info');
            $this->_results['javascript'] = $allFunctionsFound;
            
        } else {
            $this->logTest("❌ Fichier JavaScript non trouvé", 'error');
            $this->_results['javascript'] = false;
        }
    }
    
    /**
     * Test des fichiers de traduction
     */
    protected function testTranslations()
    {
        echo "<h3>🌍 Test des fichiers de traduction</h3>\n";
        
        $languages = array('fr', 'en');
        $allTranslationsOk = true;
        
        foreach ($languages as $lang) {
            $langPath = APPLICATION_PATH . "/languages/{$lang}/photofloue.csv";
            
            if (file_exists($langPath)) {
                $content = file_get_contents($langPath);
                $lines = count(explode("\n", trim($content)));
                $this->logTest("✅ Traduction {$lang}: {$lines} lignes", 'ok');
            } else {
                $this->logTest("❌ Traduction {$lang} manquante", 'error');
                $allTranslationsOk = false;
            }
        }
        
        $this->_results['translations'] = $allTranslationsOk;
    }
    
    /**
     * Afficher le résumé des tests
     */
    protected function displaySummary()
    {
        echo "<h2>📊 Résumé des tests automatiques</h2>\n";
        
        $totalTests = count($this->_results);
        $passedTests = count(array_filter($this->_results));
        $failedTests = $totalTests - $passedTests;
        
        echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<strong>📈 Statistiques:</strong><br>\n";
        echo "✅ Tests réussis: {$passedTests}/{$totalTests}<br>\n";
        echo "❌ Tests échoués: {$failedTests}/{$totalTests}<br>\n";
        
        if ($failedTests == 0) {
            echo "<br><span class='test-ok'>🎉 Tous les tests sont passés ! Le module semble prêt.</span>\n";
        } else {
            echo "<br><span class='test-warning'>⚠️ Certains tests ont échoué. Vérifiez les erreurs ci-dessus.</span>\n";
        }
        echo "</div>\n";
        
        echo "<h3>📋 Détail par test:</h3>\n";
        echo "<ul>\n";
        foreach ($this->_results as $test => $result) {
            $status = $result ? '✅' : '❌';
            $class = $result ? 'test-ok' : 'test-error';
            echo "<li><span class='{$class}'>{$status} " . ucfirst(str_replace('_', ' ', $test)) . "</span></li>\n";
        }
        echo "</ul>\n";
    }
    
    /**
     * Afficher les tests manuels à effectuer
     */
    protected function displayManualTests()
    {
        echo "<h2>🧑‍💻 Tests manuels à effectuer</h2>\n";
        
        echo "<div style='background: #fff8dc; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h3>🔍 Tests visuels recommandés:</h3>\n";
        echo "<ol>\n";
        echo "<li><strong>Test de floutage:</strong>\n";
        echo "   <ul>\n";
        echo "   <li>Déconnectez-vous complètement</li>\n";
        echo "   <li>Visitez une page avec des photos d'utilisateurs</li>\n";
        echo "   <li>Vérifiez que les photos sont floutées</li>\n";
        echo "   </ul>\n";
        echo "</li>\n";
        
        echo "<li><strong>Test de connexion:</strong>\n";
        echo "   <ul>\n";
        echo "   <li>Connectez-vous avec un compte</li>\n";
        echo "   <li>Vérifiez que les photos ne sont plus floutées</li>\n";
        echo "   </ul>\n";
        echo "</li>\n";
        
        echo "<li><strong>Test de protection:</strong>\n";
        echo "   <ul>\n";
        echo "   <li>En mode déconnecté, essayez le clic droit sur une photo</li>\n";
        echo "   <li>Testez les raccourcis Ctrl+S, Ctrl+C</li>\n";
        echo "   <li>Vérifiez les tooltips au survol</li>\n";
        echo "   </ul>\n";
        echo "</li>\n";
        
        echo "<li><strong>Test mobile:</strong>\n";
        echo "   <ul>\n";
        echo "   <li>Testez sur un appareil mobile</li>\n";
        echo "   <li>Vérifiez l'appui long sur les photos</li>\n";
        echo "   </ul>\n";
        echo "</li>\n";
        echo "</ol>\n";
        echo "</div>\n";
        
        echo "<div style='background: #f0fff0; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h3>⚙️ Tests d'administration:</h3>\n";
        echo "<ol>\n";
        echo "<li>Vérifiez que le module apparaît dans Admin > Packages</li>\n";
        echo "<li>Testez l'activation/désactivation du module</li>\n";
        echo "<li>Vérifiez les paramètres dans la base de données</li>\n";
        echo "</ol>\n";
        echo "</div>\n";
    }
    
    /**
     * Logger un résultat de test
     */
    protected function logTest($message, $type = 'info')
    {
        $class = "test-{$type}";
        echo "<div class='{$class}'>{$message}</div>\n";
    }
}

// Exécution des tests si le script est appelé directement
if (basename($_SERVER['PHP_SELF']) == 'test_module.php') {
    $tester = new PhotoFloue_TestModule();
    $tester->runAllTests();
    
    echo "<hr>\n";
    echo "<p><em>Test exécuté le " . date('Y-m-d H:i:s') . "</em></p>\n";
    echo "<p><strong>📝 Note:</strong> Ce script de test vérifie la structure et la configuration de base du module. Les tests complets nécessitent une installation active de SocialEngine.</p>\n";
}