<?php
/**
 * PhotoBlur Module - Test Script
 *
 * Script pour tester le bon fonctionnement du module PhotoBlur
 * À exécuter depuis la racine de SocialEngine : php application/modules/PhotoBlur/test_module.php
 */

// Configuration de base
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../..'));
define('APPLICATION_ENV', 'development');

// Inclure le bootstrap de SocialEngine
require_once APPLICATION_PATH . '/boot.php';

class PhotoBlur_ModuleTest
{
    public function runTests()
    {
        echo "=== PhotoBlur Module Test Suite ===\n\n";
        
        $this->testModuleStructure();
        $this->testPluginClass();
        $this->testUserStatus();
        $this->testBlurLogic();
        
        echo "\n=== Tests terminés ===\n";
    }
    
    /**
     * Test de la structure du module
     */
    private function testModuleStructure()
    {
        echo "1. Test de la structure du module...\n";
        
        $files = array(
            'Bootstrap.php',
            'Plugin/Core.php',
            'View/Helper/ItemBackgroundPhoto.php',
            'externals/scripts/photoblur.js',
            'externals/styles/photoblur.css',
            'settings/manifest.php',
            'settings/install.php'
        );
        
        $basePath = APPLICATION_PATH . '/modules/PhotoBlur/';
        
        foreach ($files as $file) {
            if (file_exists($basePath . $file)) {
                echo "  ✓ {$file} trouvé\n";
            } else {
                echo "  ✗ {$file} manquant\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Test de la classe plugin
     */
    private function testPluginClass()
    {
        echo "2. Test de la classe plugin...\n";
        
        try {
            if (class_exists('PhotoBlur_Plugin_Core')) {
                echo "  ✓ Classe PhotoBlur_Plugin_Core chargée\n";
                
                // Test des méthodes statiques
                if (method_exists('PhotoBlur_Plugin_Core', 'shouldBlurPhoto')) {
                    echo "  ✓ Méthode shouldBlurPhoto() existe\n";
                } else {
                    echo "  ✗ Méthode shouldBlurPhoto() manquante\n";
                }
                
                if (method_exists('PhotoBlur_Plugin_Core', 'applyBlurClasses')) {
                    echo "  ✓ Méthode applyBlurClasses() existe\n";
                } else {
                    echo "  ✗ Méthode applyBlurClasses() manquante\n";
                }
            } else {
                echo "  ✗ Classe PhotoBlur_Plugin_Core non trouvée\n";
            }
        } catch (Exception $e) {
            echo "  ✗ Erreur lors du test de la classe : " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test du statut utilisateur
     */
    private function testUserStatus()
    {
        echo "3. Test du statut utilisateur...\n";
        
        try {
            // Simuler un utilisateur non connecté
            if (class_exists('PhotoBlur_Plugin_Core')) {
                $shouldBlur = PhotoBlur_Plugin_Core::shouldBlurPhoto();
                if ($shouldBlur) {
                    echo "  ✓ Floutage activé pour visiteur non connecté\n";
                } else {
                    echo "  ⚠ Floutage non activé (utilisateur peut-être connecté)\n";
                }
            }
        } catch (Exception $e) {
            echo "  ✗ Erreur lors du test du statut : " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test de la logique de floutage
     */
    private function testBlurLogic()
    {
        echo "4. Test de la logique de floutage...\n";
        
        try {
            if (class_exists('PhotoBlur_Plugin_Core')) {
                // Test avec une image simple
                $testImg = '<img src="test.jpg" alt="test">';
                $result = PhotoBlur_Plugin_Core::applyBlurClasses($testImg);
                
                if (strpos($result, 'photoblur-blurred') !== false) {
                    echo "  ✓ Classes de floutage appliquées\n";
                } else {
                    echo "  ⚠ Classes de floutage non appliquées (utilisateur connecté ?)\n";
                }
                
                if (strpos($result, 'photoblur-container') !== false) {
                    echo "  ✓ Conteneur avec tooltip créé\n";
                } else {
                    echo "  ⚠ Conteneur non créé (utilisateur connecté ?)\n";
                }
            }
        } catch (Exception $e) {
            echo "  ✗ Erreur lors du test de floutage : " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
}

// Exécuter les tests
try {
    $test = new PhotoBlur_ModuleTest();
    $test->runTests();
} catch (Exception $e) {
    echo "Erreur lors de l'exécution des tests : " . $e->getMessage() . "\n";
}