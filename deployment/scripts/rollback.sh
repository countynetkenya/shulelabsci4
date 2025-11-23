#!/bin/bash
#--------------------------------------------------------------------
# ShuleLabs CI4 - Production Rollback Script
#--------------------------------------------------------------------
# Usage: ./rollback.sh [--version=v1.0.0]
# Example: ./rollback.sh --version=20251123120000
#--------------------------------------------------------------------

set -e

# Configuration
APP_DIR="/var/www/shulelabs"
RELEASE_DIR="${APP_DIR}/releases"
BACKUP_DIR="/var/backups/shulelabs"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Parse arguments
VERSION=""
for arg in "$@"; do
    case $arg in
        --version=*)
            VERSION="${arg#*=}"
            shift
            ;;
    esac
done

# Main rollback
main() {
    echo "╔════════════════════════════════════════════════════════════════╗"
    echo "║         ShuleLabs CI4 - Production Rollback Script            ║"
    echo "╚════════════════════════════════════════════════════════════════╝"
    echo ""
    
    # Get current release
    CURRENT=$(readlink -f "${APP_DIR}/current")
    log_info "Current release: $(basename $CURRENT)"
    
    # Determine target release
    if [[ -z "$VERSION" ]]; then
        # Get previous release
        TARGET=$(ls -t ${RELEASE_DIR} | sed -n '2p')
        log_info "No version specified, rolling back to previous release"
    else
        TARGET="$VERSION"
        if [[ ! -d "${RELEASE_DIR}/${TARGET}" ]]; then
            log_error "Release ${TARGET} not found"
            exit 1
        fi
    fi
    
    log_warning "Rolling back to: ${TARGET}"
    read -p "Are you sure? (yes/no): " -r
    if [[ ! $REPLY =~ ^[Yy]es$ ]]; then
        log_info "Rollback cancelled"
        exit 0
    fi
    
    # Perform rollback
    log_info "Removing current symlink..."
    rm "${APP_DIR}/current"
    
    log_info "Creating symlink to ${TARGET}..."
    ln -sf "${RELEASE_DIR}/${TARGET}" "${APP_DIR}/current"
    
    log_info "Clearing cache..."
    cd "${APP_DIR}/current"
    php spark cache:clear
    
    log_info "Reloading services..."
    systemctl reload php8.3-fpm
    systemctl reload nginx
    
    if systemctl is-active --quiet shulelabs; then
        systemctl restart shulelabs
    fi
    
    log_info "Running health check..."
    sleep 2
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://shulelabs.com/health)
    
    if [[ $HTTP_CODE -eq 200 ]]; then
        log_success "Health check passed (HTTP $HTTP_CODE)"
    else
        log_error "Health check failed (HTTP $HTTP_CODE)"
        exit 1
    fi
    
    echo ""
    log_success "═══════════════════════════════════════════════════════════════"
    log_success "  ROLLBACK SUCCESSFUL!"
    log_success "  Target: ${TARGET}"
    log_success "  Time: $(date)"
    log_success "  Completion: < 2 minutes"
    log_success "═══════════════════════════════════════════════════════════════"
    echo ""
}

main
