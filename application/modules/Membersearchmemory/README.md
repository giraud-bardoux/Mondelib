# Member Search Memory Module

Module pour SocialEngine 7.4 qui sauvegarde automatiquement les paramètres de recherche de membres.

## Fonctionnalités

- Sauvegarde automatique des critères de recherche dans un cookie
- Restauration automatique lors du retour sur la page
- Bouton de réinitialisation pour effacer les critères sauvegardés
- Expiration du cookie après 30 jours
- Aucune modification du code core de SocialEngine

## Installation

1. Copiez le dossier `Membersearchmemory` dans `/application/modules/`
2. Connectez-vous au panneau d'administration
3. Allez dans "Manage" > "Packages & Plugins"
4. Installez et activez le module "Member Search Memory"
5. Videz le cache

## Structure des fichiers

```
Membersearchmemory/
├── Bootstrap.php
├── Plugin/
│   └── Core.php
├── settings/
│   └── manifest.php
└── README.md
```

## Support

Pour toute question, consultez le fichier INSTALLATION_MEMBER_SEARCH_MEMORY.md à la racine du projet.