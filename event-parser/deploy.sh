#!/bin/bash

# Script de déploiement pour le Parseur d'Événements - Monde Libertin
# Version 1.0.0

set -e

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="Parseur d'Événements"
APP_VERSION="1.0.0"
DEFAULT_PATH="/var/www/html/event-parser"

# Fonctions utilitaires
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Vérification des prérequis
check_prerequisites() {
    log_info "Vérification des prérequis..."
    
    # Vérifier si on est root
    if [[ $EUID -eq 0 ]]; then
        log_warning "Ce script ne doit pas être exécuté en tant que root"
        exit 1
    fi
    
    # Vérifier les commandes nécessaires
    commands=("curl" "wget" "unzip")
    for cmd in "${commands[@]}"; do
        if ! command -v $cmd &> /dev/null; then
            log_warning "Commande $cmd non trouvée, installation..."
            if command -v apt-get &> /dev/null; then
                sudo apt-get update && sudo apt-get install -y $cmd
            elif command -v yum &> /dev/null; then
                sudo yum install -y $cmd
            else
                log_error "Impossible d'installer $cmd automatiquement"
                exit 1
            fi
        fi
    done
    
    log_success "Prérequis vérifiés"
}

# Création du répertoire de destination
create_directory() {
    local target_path=${1:-$DEFAULT_PATH}
    
    log_info "Création du répertoire de destination: $target_path"
    
    if [ ! -d "$target_path" ]; then
        sudo mkdir -p "$target_path"
        log_success "Répertoire créé"
    else
        log_warning "Le répertoire existe déjà"
    fi
    
    # Définir les permissions
    sudo chown -R $USER:$USER "$target_path"
    sudo chmod -R 755 "$target_path"
    
    echo "$target_path"
}

# Copie des fichiers
copy_files() {
    local target_path=$1
    
    log_info "Copie des fichiers vers $target_path"
    
    # Liste des fichiers à copier
    files=(
        "index.html"
        "styles.css"
        "app.js"
        "parser.js"
        "config.js"
        "README.md"
        ".htaccess"
        "test.html"
    )
    
    for file in "${files[@]}"; do
        if [ -f "$file" ]; then
            cp "$file" "$target_path/"
            log_success "Copié: $file"
        else
            log_error "Fichier manquant: $file"
            exit 1
        fi
    done
    
    log_success "Tous les fichiers ont été copiés"
}

# Configuration Apache
configure_apache() {
    local target_path=$1
    
    log_info "Configuration d'Apache..."
    
    # Vérifier si Apache est installé
    if ! command -v apache2 &> /dev/null && ! command -v httpd &> /dev/null; then
        log_warning "Apache n'est pas installé"
        read -p "Voulez-vous installer Apache ? (y/n): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            if command -v apt-get &> /dev/null; then
                sudo apt-get update && sudo apt-get install -y apache2
            elif command -v yum &> /dev/null; then
                sudo yum install -y httpd
            fi
        else
            log_warning "Apache n'est pas installé. L'application ne fonctionnera pas."
            return
        fi
    fi
    
    # Créer un VirtualHost si nécessaire
    local domain="mondelibertin.com"
    local vhost_file="/etc/apache2/sites-available/event-parser.conf"
    
    if [ -f "/etc/apache2/sites-available/event-parser.conf" ]; then
        log_warning "VirtualHost existe déjà"
    else
        log_info "Création du VirtualHost..."
        sudo tee "$vhost_file" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName event-parser.$domain
    DocumentRoot $target_path
    
    <Directory $target_path>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/event-parser_error.log
    CustomLog \${APACHE_LOG_DIR}/event-parser_access.log combined
</VirtualHost>
EOF
        sudo a2ensite event-parser
        sudo systemctl reload apache2
        log_success "VirtualHost configuré"
    fi
}

# Configuration des permissions
set_permissions() {
    local target_path=$1
    
    log_info "Configuration des permissions..."
    
    # Permissions pour les fichiers
    sudo find "$target_path" -type f -exec chmod 644 {} \;
    sudo find "$target_path" -type d -exec chmod 755 {} \;
    
    # Permissions spéciales pour .htaccess
    sudo chmod 644 "$target_path/.htaccess"
    
    # Propriétaire
    sudo chown -R www-data:www-data "$target_path" 2>/dev/null || \
    sudo chown -R apache:apache "$target_path" 2>/dev/null || \
    log_warning "Impossible de changer le propriétaire"
    
    log_success "Permissions configurées"
}

# Test de l'installation
test_installation() {
    local target_path=$1
    
    log_info "Test de l'installation..."
    
    # Vérifier que les fichiers sont présents
    local required_files=("index.html" "app.js" "parser.js" "config.js")
    for file in "${required_files[@]}"; do
        if [ ! -f "$target_path/$file" ]; then
            log_error "Fichier manquant après installation: $file"
            return 1
        fi
    done
    
    # Test de l'application
    if command -v curl &> /dev/null; then
        local test_url="http://localhost/event-parser/"
        if curl -s -o /dev/null -w "%{http_code}" "$test_url" | grep -q "200"; then
            log_success "Application accessible via HTTP"
        else
            log_warning "Application non accessible via HTTP (normal si Apache n'est pas configuré)"
        fi
    fi
    
    log_success "Installation testée avec succès"
}

# Affichage des informations finales
show_final_info() {
    local target_path=$1
    
    log_success "Installation terminée avec succès !"
    echo
    echo "=== INFORMATIONS D'INSTALLATION ==="
    echo "Application: $APP_NAME v$APP_VERSION"
    echo "Répertoire: $target_path"
    echo "URL d'accès: http://localhost/event-parser/"
    echo "URL de test: http://localhost/event-parser/test.html"
    echo
    echo "=== CONFIGURATION ==="
    echo "1. Ouvrez http://localhost/event-parser/ dans votre navigateur"
    echo "2. Configurez l'URL de publication: https://mondelibertin.com/events/create"
    echo "3. Testez avec une URL d'événement"
    echo
    echo "=== DÉPANNAGE ==="
    echo "Si l'application ne fonctionne pas:"
    echo "- Vérifiez que Apache est installé et démarré"
    echo "- Vérifiez les permissions du répertoire"
    echo "- Consultez les logs Apache: /var/log/apache2/error.log"
    echo
    echo "=== SUPPORT ==="
    echo "Pour toute question, consultez le README.md dans le répertoire d'installation"
}

# Fonction principale
main() {
    echo "=================================="
    echo "  $APP_NAME - Installation"
    echo "  Version: $APP_VERSION"
    echo "=================================="
    echo
    
    # Demander le chemin d'installation
    read -p "Chemin d'installation [$DEFAULT_PATH]: " install_path
    install_path=${install_path:-$DEFAULT_PATH}
    
    # Vérifications
    check_prerequisites
    
    # Installation
    create_directory "$install_path"
    copy_files "$install_path"
    configure_apache "$install_path"
    set_permissions "$install_path"
    test_installation "$install_path"
    
    # Final
    show_final_info "$install_path"
}

# Gestion des arguments
case "${1:-}" in
    --help|-h)
        echo "Usage: $0 [OPTIONS]"
        echo "Options:"
        echo "  --help, -h     Afficher cette aide"
        echo "  --path PATH    Spécifier le chemin d'installation"
        echo "  --auto         Installation automatique sans questions"
        exit 0
        ;;
    --path)
        if [ -z "$2" ]; then
            log_error "Chemin manquant pour --path"
            exit 1
        fi
        DEFAULT_PATH="$2"
        shift 2
        ;;
    --auto)
        # Installation automatique
        check_prerequisites
        install_path=$(create_directory)
        copy_files "$install_path"
        set_permissions "$install_path"
        test_installation "$install_path"
        show_final_info "$install_path"
        exit 0
        ;;
    *)
        main
        ;;
esac