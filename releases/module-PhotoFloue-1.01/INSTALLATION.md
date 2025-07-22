# 📦 PhotoFloue Module v1.0.1 - Installation

## 🎯 Module de floutage des photos pour SocialEngine 7.4

**Version :** 1.0.1  
**Compatibilité :** SocialEngine 7.4+  
**Date :** Janvier 2025  

## 📋 Fonctionnalités

✅ **Floutage automatique** des photos pour visiteurs non connectés  
✅ **Protection anti-capture** avancée (clic droit, raccourcis)  
✅ **Messages d'incitation** à la connexion  
✅ **Protection mobile** tactile  
✅ **Configuration complète** via paramètres  

## 🚀 Installation rapide

### 1. Prérequis
- SocialEngine 7.4 ou supérieur
- Modules `user` et `album` activés
- PHP 7.0+ recommandé
- Accès administrateur

### 2. Installation
```bash
# Décompresser dans le répertoire modules
unzip module-PhotoFloue-1.01.zip
cp -r PhotoFloue /path/to/socialengine/application/modules/

# Copier les traductions
cp languages/*.csv /path/to/socialengine/application/languages/fr/
cp languages/*.csv /path/to/socialengine/application/languages/en/

# Ajuster les permissions
chmod -R 755 /path/to/socialengine/application/modules/PhotoFloue
```

### 3. Activation
1. Connectez-vous en administrateur
2. Allez dans **Admin Panel > Plugins > Browse Plugins**
3. Trouvez "PhotoFloue Module v1.0.1"
4. Cliquez **Install** puis **Enable**

### 4. Configuration
Les paramètres par défaut sont optimaux. Pour personnaliser :

```sql
-- Changer l'intensité du flou (1-20)
UPDATE engine4_core_settings SET value = '15' WHERE name = 'photofloue.blur_intensity';

-- Personnaliser le message
UPDATE engine4_core_settings SET value = 'Votre message' WHERE name = 'photofloue.login_message';
```

## 🧪 Test de fonctionnement

1. **Déconnectez-vous** complètement du site
2. **Visitez une page** avec des photos d'albums/utilisateurs
3. **Vérifiez** que les photos sont floutées
4. **Reconnectez-vous** et vérifiez que les photos sont nettes

## ⚙️ Paramètres disponibles

| Paramètre | Description | Défaut |
|-----------|-------------|---------|
| `photofloue.enabled` | Activer/désactiver | `1` |
| `photofloue.blur_intensity` | Intensité flou (px) | `10` |
| `photofloue.apply_to_users` | Photos utilisateurs | `1` |
| `photofloue.apply_to_albums` | Photos albums | `1` |
| `photofloue.mobile_protection` | Protection mobile | `1` |
| `photofloue.login_message` | Message incitatif | `"Connectez-vous..."` |

## 🔧 Dépannage

### Module ne s'installe pas
1. Vérifiez les permissions : `chmod 755 application/modules/PhotoFloue`
2. Videz le cache : `rm -rf temporary/cache/*`
3. Vérifiez les dépendances : modules `user` et `album` activés

### Photos non floutées
1. Vérifiez que vous êtes bien déconnecté
2. Testez en navigation privée
3. Contrôlez les paramètres : `SELECT * FROM engine4_core_settings WHERE name LIKE 'photofloue.%';`

### Erreur d'installation
1. **Désactivez** le module via base de données
2. **Supprimez** les fichiers : `rm -rf application/modules/PhotoFloue`
3. **Nettoyez** la base : `DELETE FROM engine4_core_settings WHERE name LIKE 'photofloue.%';`
4. **Réinstallez** proprement

## 🆘 Solution de secours

Si l'installation échoue, utilisez la **solution CSS directe** :

```css
/* Ajouter dans Admin > CSS personnalisé */
body:not(.logged-in) .bg_item_photo_album_photo {
    filter: blur(10px) !important;
}
body:not(.logged-in) .profile_photo img {
    filter: blur(10px) !important;
}
```

## 📞 Support

- **Documentation complète** : README.md inclus
- **Script de test** : `php test_module.php`
- **Diagnostic** : `./debug_photofloue_error.sh`

## 📊 Statistiques

- **Taille** : ~50KB
- **Impact performance** : Minimal (CSS/JS conditionnel)
- **Compatibilité** : Tous navigateurs modernes
- **Mobile** : iOS/Android compatible

---

**Développé pour SocialEngine 7.4** | **v1.0.1** | **© 2025**