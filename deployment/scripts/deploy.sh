#!/bin/bash
#--------------------------------------------------------------------
# ShuleLabs CI4 - Production Deployment Script
#--------------------------------------------------------------------
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production
#--------------------------------------------------------------------

set -e  # Exit on error
set -u  # Exit on undefined variable

# Configuration
ENVIRONMENT="${1:-production}"
APP_DIR="/var/www/shulelabs"
BACKUP_DIR="/var/backups/shulelabs"
RELEASE_DIR="${APP_DIR}/releases"
CURRENT_RELEASE="${RELEASE_DIR}/$(date +%Y%m%d%H%M%S)"
GIT_REPO="https://github.com/countynetkenya/shulelabsci4.git"
GIT_BRANCH="main"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
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

# Pre-deployment checks
pre_deployment_checks() {
    log_info "Running pre-deployment checks..."
    
    # Check if running as www-data or root
    if [[ $EUID -ne 0 ]] && [[ $(whoami) != "www-data" ]]; then
        log_error "This script must be run as root or www-data user"
        exit 1
    fi
    
    # Check required commands
    for cmd in git php composer mysql nginx; do
        if ! command -v $cmd &> /dev/null; then
            log_error "$cmd is not installed"
            exit 1
        fi
    done
    
    # Check disk space (require at least 1GB free)
    FREE_SPACE=$(df -BG ${APP_DIR} | awk 'NR==2 {print $4}' | sed 's/G//')
    if [[ $FREE_SPACE -lt 1 ]]; then
        log_error "Insufficient disk space. Required: 1GB, Available: ${FREE_SPACE}GB"
        exit 1
    fi
    
    log_success "Pre-deployment checks passed"
}

# Create backup
create_backup() {
    log_info "Creating backup..."
    
    BACKUP_NAME="backup_$(date +%Y%m%d%H%M%S)"
    mkdir -p "${BACKUP_DIR}/${BACKUP_NAME}"
    
    # Backup current application
    if [[ -L "${APP_DIR}/current" ]]; then
        cp -rL "${APP_DIR}/current" "${BACKUP_DIR}/${BACKUP_NAME}/app"
        log_success "Application backup created"
    fi
    
    # Backup database
    DB_NAME=$(grep "database.default.database" ${APP_DIR}/.env | cut -d'=' -f2 | tr -d ' ')
    DB_USER=$(grep "database.default.username" ${APP_DIR}/.env | cut -d'=' -f2 | tr -d ' ')
    DB_PASS=$(grep "database.default.password" ${APP_DIR}/.env | cut -d'=' -f2 | tr -d ' ')
    
    mysqldump -u${DB_USER} -p${DB_PASS} ${DB_NAME} | gzip > "${BACKUP_DIR}/${BACKUP_NAME}/database.sql.gz"
    log_success "Database backup created"
    
    # Keep only last 7 backups
    cd ${BACKUP_DIR}
    ls -t | tail -n +8 | xargs -r rm -rf
    
    log_success "Backup created: ${BACKUP_NAME}"
}

# Clone repository
clone_repository() {
    log_info "Cloning repository..."
    
    mkdir -p ${RELEASE_DIR}
    git clone --branch ${GIT_BRANCH} --depth 1 ${GIT_REPO} ${CURRENT_RELEASE}
    
    log_success "Repository cloned to ${CURRENT_RELEASE}"
}

# Install dependencies
install_dependencies() {
    log_info "Installing dependencies..."
    
    cd ${CURRENT_RELEASE}
    
    # Composer install
    composer install --no-dev --optimize-autoloader --no-interaction
    
    log_success "Dependencies installed"
}

# Configure environment
configure_environment() {
    log_info "Configuring environment..."
    
    # Copy .env file
    if [[ ! -f "${APP_DIR}/.env" ]]; then
        log_error ".env file not found in ${APP_DIR}"
        exit 1
    fi
    
    cp "${APP_DIR}/.env" "${CURRENT_RELEASE}/.env"
    
    # Set proper permissions
    chmod 640 "${CURRENT_RELEASE}/.env"
    
    log_success "Environment configured"
}

# Run migrations
run_migrations() {
    log_info "Running database migrations..."
    
    cd ${CURRENT_RELEASE}
    php spark migrate --all
    
    log_success "Migrations completed"
}

# Clear cache
clear_cache() {
    log_info "Clearing cache..."
    
    cd ${CURRENT_RELEASE}
    php spark cache:clear
    
    log_success "Cache cleared"
}

# Set permissions
set_permissions() {
    log_info "Setting permissions..."
    
    chown -R www-data:www-data ${CURRENT_RELEASE}
    chmod -R 755 ${CURRENT_RELEASE}
    chmod -R 775 ${CURRENT_RELEASE}/writable
    
    log_success "Permissions set"
}

# Symlink new release
symlink_release() {
    log_info "Symlinking new release..."
    
    # Remove old symlink
    if [[ -L "${APP_DIR}/current" ]]; then
        rm "${APP_DIR}/current"
    fi
    
    # Create new symlink
    ln -sf ${CURRENT_RELEASE} "${APP_DIR}/current"
    
    log_success "New release symlinked"
}

# Reload services
reload_services() {
    log_info "Reloading services..."
    
    # Reload PHP-FPM
    systemctl reload php8.3-fpm
    
    # Reload Nginx
    systemctl reload nginx
    
    # Restart application service (if exists)
    if systemctl is-active --quiet shulelabs; then
        systemctl restart shulelabs
    fi
    
    log_success "Services reloaded"
}

# Health check
health_check() {
    log_info "Running health check..."
    
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://shulelabs.com/health)
    
    if [[ $HTTP_CODE -eq 200 ]]; then
        log_success "Health check passed (HTTP $HTTP_CODE)"
    else
        log_error "Health check failed (HTTP $HTTP_CODE)"
        exit 1
    fi
}

# Cleanup old releases
cleanup_old_releases() {
    log_info "Cleaning up old releases..."
    
    cd ${RELEASE_DIR}
    ls -t | tail -n +6 | xargs -r rm -rf
    
    log_success "Old releases cleaned up (kept last 5)"
}

# Rollback function
rollback() {
    log_warning "Rolling back to previous release..."
    
    PREVIOUS_RELEASE=$(ls -t ${RELEASE_DIR} | sed -n '2p')
    
    if [[ -z "$PREVIOUS_RELEASE" ]]; then
        log_error "No previous release found for rollback"
        exit 1
    fi
    
    rm "${APP_DIR}/current"
    ln -sf "${RELEASE_DIR}/${PREVIOUS_RELEASE}" "${APP_DIR}/current"
    
    reload_services
    
    log_success "Rolled back to ${PREVIOUS_RELEASE}"
}

# Main deployment process
main() {
    echo "╔════════════════════════════════════════════════════════════════╗"
    echo "║         ShuleLabs CI4 - Production Deployment Script          ║"
    echo "╚════════════════════════════════════════════════════════════════╝"
    echo ""
    log_info "Starting deployment to ${ENVIRONMENT}..."
    echo ""
    
    # Trap errors and rollback
    trap 'log_error "Deployment failed! Rolling back..."; rollback; exit 1' ERR
    
    pre_deployment_checks
    create_backup
    clone_repository
    install_dependencies
    configure_environment
    run_migrations
    clear_cache
    set_permissions
    symlink_release
    reload_services
    health_check
    cleanup_old_releases
    
    echo ""
    log_success "═══════════════════════════════════════════════════════════════"
    log_success "  DEPLOYMENT SUCCESSFUL!"
    log_success "  Release: ${CURRENT_RELEASE}"
    log_success "  Time: $(date)"
    log_success "═══════════════════════════════════════════════════════════════"
    echo ""
}

# Run main function
main
