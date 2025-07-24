# Module Photo Blur pour SocialEngine 7.4

Ce module permet aux membres de flouter des photos dans les albums.

## Fonctionnalités

- Ajout d'un lien "Flouter cette photo" dans le menu d'options des photos
- Interface simple pour choisir le niveau de flou (de 1 à 10)
- Prévisualisation avant/après le floutage
- Téléchargement de la photo floutée
- Historique des photos floutées dans l'administration

## Installation

1. Copiez le dossier `Photoblur` dans `application/modules/`
2. Connectez-vous à l'administration de SocialEngine
3. Allez dans "Manage" > "Packages & Plugins"
4. Trouvez "Photo Blur" et cliquez sur "Install"
5. Le module est maintenant actif

## Utilisation

1. Les membres connectés verront un lien "Flouter cette photo" dans le menu d'options de chaque photo
2. En cliquant sur ce lien, ils accèdent à une interface pour choisir le niveau de flou
3. Après validation, la photo floutée est générée et peut être téléchargée
4. Les photos originales ne sont jamais modifiées

## Configuration

Aucune configuration n'est nécessaire. Le module fonctionne immédiatement après l'installation.

## Support

Ce module utilise les fonctions GD de PHP pour le traitement d'image. Assurez-vous que l'extension GD est installée et activée sur votre serveur.

## Licence

Ce module est fourni tel quel, sans garantie.