# 🎯 Plugin EventFix - Intégration SocialEngine 7.4

## Vue d'ensemble

Ce repository contient le plugin **EventFix** développé pour étendre la plage d'années sélectionnable dans les formulaires de création et d'édition d'événements de SocialEngine 7.4.

## 🚀 Plugin installé dans cette branche

### Emplacement
```
application/modules/EventFix/
```

### Version
**EventFix v1.0.0** - Plugin fonctionnel et prêt pour production

## ✅ Fonctionnalités

- ✅ Étend automatiquement la plage d'années des formulaires Event
- ✅ Permet la sélection de dates jusqu'à **5 ans en arrière**
- ✅ **Non-intrusif** : aucune modification du code SocialEngine existant
- ✅ Compatible avec **toutes les mises à jour** SocialEngine
- ✅ Support des formulaires **AJAX** et chargement dynamique
- ✅ Détection automatique multi-pattern des formulaires

## 🔧 Installation (si vous clonez ce repo)

Le plugin est déjà intégré dans cette branche. Pour l'activer :

### 1. Interface d'administration
1. Connectez-vous à l'admin SocialEngine
2. Allez dans **Admin Panel** > **Plugins**
3. Trouvez **"Event Date Range Fix"**
4. Cliquez sur **Install**
5. Cliquez sur **Enable**

### 2. Vérification
- Allez sur une page de création d'événement
- Vérifiez que le sélecteur d'année permet 5 ans en arrière
- Console navigateur (F12) : messages `EventFix: Étendu...`

## 📁 Structure du plugin

```
application/modules/EventFix/
├── Bootstrap.php                    # Initialisation du module
├── Plugin/
│   ├── Core.php                    # Plugin principal avec JavaScript
│   └── index.html                  # Sécurité
├── settings/
│   ├── manifest.php                # Configuration SocialEngine
│   ├── install.php                 # Script d'installation
│   └── index.html                  # Sécurité
├── README.md                       # Documentation utilisateur
├── INSTALLATION.md                 # Guide d'installation détaillé
└── RESUME_PLUGIN.md               # Documentation technique
```

## 🔧 Configuration

### Modifier le nombre d'années en arrière
Dans `application/modules/EventFix/Plugin/Core.php`, ligne ~74 :
```javascript
var minYear = currentYear - 5; // Changer 5 par le nombre souhaité
```

## 📦 Archives de distribution

Les archives prêtes à installer sont disponibles dans le dossier `releases/` :

- **`EventFix-v1.0.0-FINAL.zip`** - Archive ZIP recommandée
- **`EventFix-v1.0.0-FINAL.tar.gz`** - Archive TAR.GZ alternative
- **`LIVRAISON_EVENTFIX.md`** - Documentation complète de livraison

## 🏗️ Fonctionnement technique

### Approche JavaScript non-intrusive
1. **Hook SocialEngine** : `onRenderLayoutDefault` injecte le JavaScript
2. **Détection automatique** : 8 patterns CSS pour trouver les sélecteurs d'année
3. **Modification dynamique** : Ajout des années manquantes
4. **Support AJAX** : `MutationObserver` pour formulaires dynamiques

### Sélecteurs détectés
- `select[name="starttime[year]"]` - Standard SocialEngine
- `select[name="starttime-year"]` - Format alternatif
- `select[id*="starttime"][id*="year"]` - Détection par ID
- `select[class*="starttime"][class*="year"]` - Détection par classe
- Et 4 autres patterns pour une couverture maximale

## 🎯 Avantages techniques

- **Non-intrusif** : Aucune modification du code source
- **Compatible mises à jour** : Résistant aux updates SocialEngine
- **Facilement configurable** : Une variable à modifier
- **Debugging intégré** : Logs automatiques en console
- **Performance** : Impact minimal (~8KB JavaScript)

## 🐛 Dépannage

### Le plugin ne fonctionne pas
1. Vérifiez que le module Event est installé
2. Vérifiez que JavaScript est activé
3. Console navigateur (F12) pour voir les erreurs
4. Vérifiez que le plugin EventFix est activé en admin

### Logs de débogage
Le plugin affiche des messages dans la console :
```
EventFix: Étendu la plage d'années pour select[name="starttime[year]"] de 2019 à 2030
```

## 📊 Compatibilité

- ✅ SocialEngine 7.0+ (testé 7.4)
- ✅ Tous navigateurs modernes
- ✅ Tous thèmes SocialEngine
- ✅ Formulaires AJAX et statiques

## 🔄 Branche et historique

Cette branche contient :
- Le code source SocialEngine 7.4
- Le plugin EventFix intégré
- Les archives de distribution prêtes

### Derniers commits
- Plugin EventFix intégré et fonctionnel
- Documentation complète fournie
- Archives de distribution générées

---

**Plugin EventFix v1.0.0 - Extension intelligente pour SocialEngine 7.4**  
*Développé pour élargir la sélection d'années dans les formulaires Event*