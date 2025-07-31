<?php
/**
 * EXEMPLE D'INTÉGRATION DU SYSTÈME DE MÉMORISATION DES RECHERCHES
 * 
 * Ce fichier montre comment intégrer le script member-search-memory.js
 * dans vos templates SocialEngine existants.
 * 
 * Vous pouvez copier le contenu de ce fichier dans :
 * - application/modules/User/widgets/browse-search/index.tpl
 * - application/modules/User/views/scripts/index/browse.tpl
 * - Ou tout autre template où vous voulez activer la mémorisation
 */
?>

<!-- VOTRE CONTENU EXISTANT DU TEMPLATE -->
<!-- ... contenu du template original ... -->

<!-- INTÉGRATION DU SCRIPT DE MÉMORISATION -->
<!-- À ajouter à la fin de votre template -->
<script type="text/javascript">
// Vérifier si le script principal n'est pas déjà chargé
if (typeof window.memberSearchMemory === 'undefined') {
    // Charger le script principal
    const script = document.createElement('script');
    script.src = '<?php echo $this->baseUrl() ?>/public/js/member-search-memory.js';
    script.async = true;
    document.head.appendChild(script);
} else {
    // Si déjà chargé, réinitialiser pour cette page
    window.memberSearchMemory = new MemberSearchMemory();
}
</script>

<!-- ALTERNATIVE : INTÉGRATION DIRECTE DANS LE TEMPLATE -->
<!-- Si vous préférez ne pas créer de fichier séparé, vous pouvez inclure le script directement : -->

<?php if (false): // Changez en true pour activer cette méthode ?>
<script type="text/javascript">
/**
 * Version intégrée du système de mémorisation des recherches
 * (copie du contenu de member-search-memory.js)
 */

class MemberSearchMemory {
    constructor() {
        this.cookieName = 'se_member_search_memory';
        this.historyCookieName = 'se_member_search_history';
        this.maxHistoryItems = 10;
        this.cookieExpireDays = 30;
        
        this.init();
    }
    
    // ... rest of the class code from member-search-memory.js ...
    // (copier tout le contenu de la classe depuis le fichier principal)
}

// Initialiser
window.memberSearchMemory = new MemberSearchMemory();
</script>
<?php endif; ?>

<!-- EXEMPLE D'UTILISATION AVEC VÉRIFICATIONS -->
<script type="text/javascript">
// Attendre que le script soit chargé et initialiser avec des vérifications
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si nous sommes sur une page de recherche de membres
    const isUserSearchPage = window.location.pathname.includes('/user') || 
                            window.location.pathname.includes('/members') ||
                            document.querySelector('.field_search_criteria');
    
    if (isUserSearchPage && typeof window.memberSearchMemory !== 'undefined') {
        console.log('Système de mémorisation des recherches activé pour cette page');
        
        // Optionnel : personnaliser les messages
        const originalConsoleLog = console.log;
        console.log = function(message) {
            if (typeof message === 'string' && message.includes('Member Search Memory')) {
                // Traduire les messages ou les personnaliser
                message = message.replace('Member Search Memory:', '🔍 Recherche Membres:');
            }
            originalConsoleLog.apply(console, arguments);
        };
    }
});
</script>

<!-- STYLES CSS PERSONNALISÉS (OPTIONNEL) -->
<style type="text/css">
/* Personnalisation de l'apparence du système de mémorisation */
.search-memory-container {
    /* Adapter aux couleurs de votre thème */
    border-color: #your-theme-color !important;
}

.search-memory-toggle {
    /* Utiliser les styles de boutons de votre thème */
    background: #your-button-color !important;
    color: #your-text-color !important;
}

/* Adaptation mobile */
@media (max-width: 768px) {
    .search-memory-header {
        flex-direction: column;
        gap: 5px;
    }
    
    .search-memory-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .search-memory-date {
        min-width: auto;
        font-size: 10px;
    }
}
</style>

<!-- CONFIGURATION AVANCÉE (OPTIONNEL) -->
<script type="text/javascript">
// Configuration avancée du système
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.memberSearchMemory !== 'undefined') {
        // Personnaliser les paramètres
        window.memberSearchMemory.maxHistoryItems = 15; // Plus d'historique
        window.memberSearchMemory.cookieExpireDays = 7;  // Durée plus courte
        
        // Ajouter des événements personnalisés
        document.addEventListener('searchMemoryLoaded', function(e) {
            console.log('🔍 Recherche restaurée:', e.detail);
        });
        
        // Hook pour modifier les critères avant sauvegarde
        const originalSave = window.memberSearchMemory.saveSearchCriteria;
        window.memberSearchMemory.saveSearchCriteria = function(criteria) {
            // Ajouter des métadonnées personnalisées
            criteria._custom_timestamp = new Date().toISOString();
            criteria._page_title = document.title;
            
            return originalSave.call(this, criteria);
        };
    }
});
</script>