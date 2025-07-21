#!/bin/bash

# Script d'installation automatique EventFix pour SocialEngine 7.4
# Usage: ./install.sh [chemin_vers_socialengine]

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== EventFix Plugin - Installation automatique ===${NC}"
echo

# Vérifier si un chemin est fourni
if [ "$1" ]; then
    SOCIALENGINE_PATH="$1"
else
    SOCIALENGINE_PATH="."
fi

# Vérifier que le répertoire SocialEngine existe
if [ ! -d "$SOCIALENGINE_PATH/application/modules" ]; then
    echo -e "${RED}Erreur: Le répertoire SocialEngine n'a pas été trouvé à: $SOCIALENGINE_PATH${NC}"
    echo "Usage: $0 [chemin_vers_socialengine]"
    exit 1
fi

echo -e "Installation dans: ${YELLOW}$SOCIALENGINE_PATH${NC}"
echo

# Créer le répertoire de destination
DEST_PATH="$SOCIALENGINE_PATH/application/modules/EventFix"

if [ -d "$DEST_PATH" ]; then
    echo -e "${YELLOW}Le module EventFix existe déjà. Voulez-vous le remplacer? (y/N)${NC}"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        echo "Installation annulée."
        exit 0
    fi
    echo -e "${YELLOW}Suppression de l'ancienne version...${NC}"
    rm -rf "$DEST_PATH"
fi

echo -e "${GREEN}Création du répertoire du module...${NC}"
mkdir -p "$DEST_PATH"
mkdir -p "$DEST_PATH/Plugin"
mkdir -p "$DEST_PATH/settings"

# Copier les fichiers (en assumant que nous sommes dans le répertoire EventFix)
echo -e "${GREEN}Copie des fichiers...${NC}"

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "Bootstrap.php" ] || [ ! -f "Plugin/Core.php" ]; then
    echo -e "${RED}Erreur: Veuillez exécuter ce script depuis le répertoire EventFix${NC}"
    exit 1
fi

# Copier tous les fichiers
cp -r * "$DEST_PATH/"

# Définir les permissions
echo -e "${GREEN}Configuration des permissions...${NC}"
chmod -R 755 "$DEST_PATH"

# Trouver le propriétaire web approprié
WEB_USER="www-data"
if id "apache" &>/dev/null; then
    WEB_USER="apache"
fi

# Essayer de changer le propriétaire (peut nécessiter sudo)
if command -v chown &> /dev/null; then
    echo -e "${YELLOW}Tentative de changement du propriétaire vers $WEB_USER...${NC}"
    chown -R "$WEB_USER:$WEB_USER" "$DEST_PATH" 2>/dev/null || {
        echo -e "${YELLOW}Impossible de changer le propriétaire automatiquement.${NC}"
        echo -e "${YELLOW}Exécutez manuellement: sudo chown -R $WEB_USER:$WEB_USER $DEST_PATH${NC}"
    }
fi

echo
echo -e "${GREEN}✓ Installation terminée avec succès!${NC}"
echo
echo -e "${YELLOW}Prochaines étapes:${NC}"
echo "1. Connectez-vous à l'administration de SocialEngine"
echo "2. Allez dans Admin Panel > Plugins (ou Modules)"
echo "3. Trouvez 'Event Date Range Fix' et cliquez sur Install"
echo "4. Activez le module en cliquant sur Enable"
echo
echo -e "${GREEN}Structure installée:${NC}"
find "$DEST_PATH" -type f | sed "s|$DEST_PATH|EventFix|" | sort

echo
echo -e "${YELLOW}Pour vérifier l'installation:${NC}"
echo "- Allez sur une page de création d'événement"
echo "- Vérifiez que le sélecteur d'année permet 5 ans en arrière"
echo "- Ouvrez la console du navigateur (F12) pour voir les logs EventFix"
echo
echo -e "${GREEN}Installation terminée!${NC}"