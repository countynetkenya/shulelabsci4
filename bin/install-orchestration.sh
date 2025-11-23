#!/bin/bash
################################################################################
# ShuleLabs AI Orchestration Installation Script
# Version: 2.0.0
# Description: One-command setup for production-ready AI orchestration system
################################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Banner
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║         ShuleLabs AI Orchestration System v2.0                     ║"
echo "║         Production-Ready Autonomous Development                    ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""

# Detect project root
if [ -f "composer.json" ]; then
    PROJECT_ROOT=$(pwd)
elif [ -f "../composer.json" ]; then
    PROJECT_ROOT=$(cd .. && pwd)
else
    log_error "Could not find composer.json. Please run from project root or bin/ directory."
    exit 1
fi

log_info "Project root: $PROJECT_ROOT"
cd "$PROJECT_ROOT"

# Create orchestration directory structure
log_info "Creating orchestration directory structure..."
mkdir -p .orchestration/{logs,checkpoints,templates,reports}
mkdir -p bin/phases
mkdir -p docs/archive/old-orchestrations

# Set permissions
chmod +x bin/orchestrate.sh 2>/dev/null || true
chmod +x bin/phases/*.sh 2>/dev/null || true

log_success "Directory structure created"

# Validate configuration files
log_info "Validating configuration files..."

if [ ! -f ".orchestration.env" ]; then
    log_warning ".orchestration.env not found. Creating default..."
    cp .orchestration.env.example .orchestration.env 2>/dev/null || true
fi

if [ ! -f "orchestration.json" ]; then
    log_error "orchestration.json not found. Please ensure it exists in project root."
    exit 1
fi

log_success "Configuration validated"

# Check dependencies
log_info "Checking dependencies..."

check_command() {
    if command -v "$1" &> /dev/null; then
        log_success "$1 found ($(command -v $1))"
        return 0
    else
        log_error "$1 not found. Please install it."
        return 1
    fi
}

DEPS_OK=true
check_command php || DEPS_OK=false
check_command composer || DEPS_OK=false
check_command git || DEPS_OK=false

if [ "$DEPS_OK" = false ]; then
    log_error "Missing required dependencies. Installation cannot continue."
    exit 1
fi

# Check PHP version
log_info "Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")

if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 1 ]); then
    log_error "PHP 8.1+ required. Found: $PHP_VERSION"
    exit 1
fi

log_success "PHP $PHP_VERSION is compatible"

# Check required PHP extensions
log_info "Checking PHP extensions..."
REQUIRED_EXTENSIONS=("intl" "mbstring" "json" "curl" "mysqli" "pdo")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "^$ext$"; then
        log_success "$ext extension found"
    else
        log_warning "$ext extension missing"
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
    log_warning "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
    log_info "Install with: sudo apt-get install php-${MISSING_EXTENSIONS[0]} (or equivalent)"
fi

# Install Composer dependencies
log_info "Installing Composer dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
    log_success "Composer dependencies installed"
else
    log_warning "No composer.json found. Skipping dependency installation."
fi

# Create .gitignore entries for orchestration
log_info "Updating .gitignore..."
if [ ! -f ".gitignore" ]; then
    touch .gitignore
fi

if ! grep -q ".orchestration/logs" .gitignore; then
    cat >> .gitignore << 'EOF'

# AI Orchestration
.orchestration/logs/
.orchestration/checkpoints/
.orchestration/reports/
orchestration-*.log
EOF
    log_success ".gitignore updated"
else
    log_info ".gitignore already configured"
fi

# Archive old orchestration documents
log_info "Archiving old orchestration documents..."
OLD_DOCS=(
    "SUPER_DEVELOPER_MULTISCHOOL_PROMPT.md"
    "MULTISCHOOL_FINAL_SUMMARY.md"
    "MULTISCHOOL_PROGRESS_REPORT.md"
    "BUILD_COMPLETE.md"
    "SESSION_CHANGELOG.md"
)

ARCHIVED_COUNT=0
for doc in "${OLD_DOCS[@]}"; do
    if [ -f "$doc" ]; then
        mv "$doc" "docs/archive/old-orchestrations/"
        log_success "Archived $doc"
        ((ARCHIVED_COUNT++))
    fi
done

if [ $ARCHIVED_COUNT -gt 0 ]; then
    log_success "Archived $ARCHIVED_COUNT old orchestration document(s)"
    
    # Create migration note
    cat > docs/archive/old-orchestrations/MIGRATION_NOTE.md << 'EOF'
# Old Orchestration Documents - Archived

**Date Archived**: $(date +%Y-%m-%d)
**Reason**: Migration to AI Orchestration Blueprint v2.0

## Summary

These documents represent previous orchestration approaches that have been superseded by the comprehensive **AI Orchestration Blueprint v2.0**.

## What Changed

The new orchestration system provides:
- ✅ Pre-flight validation (Phase 0)
- ✅ Database-first approach (Phase 1)
- ✅ Test-driven development (Phase 2)
- ✅ Progressive building with micro-iterations (Phase 3)
- ✅ Continuous integration and validation (Phase 4)
- ✅ Automated deployment (Phase 5)
- ✅ Intelligence & monitoring (Phase 6)

## New Default

**Primary Reference**: `docs/AI_ORCHESTRATION_BLUEPRINT.md`
**Executable System**: `bin/orchestrate.sh`

## Migration Path

If you were using old orchestration prompts:

1. Review AI_ORCHESTRATION_BLUEPRINT.md for new workflow
2. Run `bin/install-orchestration.sh` to set up new system
3. Execute `bin/orchestrate.sh --project=yourproject` to start

## Archived Files

These files are preserved for historical reference but should not be used for new projects.
EOF
    log_success "Created migration note"
fi

# Validate orchestration scripts
log_info "Validating orchestration scripts..."
if [ -x "bin/orchestrate.sh" ]; then
    log_success "Main orchestration engine found and executable"
else
    log_warning "bin/orchestrate.sh not executable. Setting permissions..."
    chmod +x bin/orchestrate.sh
fi

# Count phase scripts
PHASE_SCRIPTS=$(find bin/phases -name "phase*.sh" 2>/dev/null | wc -l)
if [ "$PHASE_SCRIPTS" -gt 0 ]; then
    log_success "Found $PHASE_SCRIPTS phase script(s)"
    chmod +x bin/phases/*.sh
else
    log_warning "No phase scripts found in bin/phases/"
fi

# Create quick reference
log_info "Creating quick reference guide..."
cat > ORCHESTRATION_QUICK_START.md << 'EOF'
# AI Orchestration Quick Start

## Installation Complete! ✅

Your AI Orchestration System v2.0 is now ready to use.

## One-Command Start

```bash
./bin/orchestrate.sh --project=myproject
```

## Available Commands

### Start Full Orchestration
```bash
./bin/orchestrate.sh --project=myproject
```

### Run Specific Phase
```bash
./bin/orchestrate.sh --phase=0  # Pre-flight validation only
./bin/orchestrate.sh --phase=1  # Database setup only
./bin/orchestrate.sh --phase=2  # Test-driven development only
```

### Dry Run (No Changes)
```bash
./bin/orchestrate.sh --dry-run --project=myproject
```

### Skip Phases
```bash
./bin/orchestrate.sh --skip-phase=5  # Skip deployment
```

## What Happens

The orchestration will execute 6-7 phases:

1. **Phase 0**: Pre-Flight Validation (30 sec)
   - Checks PHP version, extensions, database, git, disk space
   
2. **Phase 1**: Database-First Foundation (2 min)
   - Creates migrations, seeds data, generates models
   
3. **Phase 2**: Test-Driven Development (3 min)
   - Writes tests first, then implements features
   
4. **Phase 3**: Progressive Building (15 min)
   - Builds features incrementally with continuous validation
   
5. **Phase 4**: Continuous Integration (2 min)
   - Runs full test suite, static analysis, security scans
   
6. **Phase 5**: Deployment (2 min)
   - Deploys to staging, runs E2E tests
   
7. **Phase 6**: Intelligence & Monitoring (ongoing)
   - Generates reports, sets up monitoring

## Success Criteria

- ✅ 85%+ test coverage
- ✅ <10 cyclomatic complexity
- ✅ <3% code duplication
- ✅ A+ security grade
- ✅ 100% data integrity

## Configuration

Edit `.orchestration.env` to customize:
- Enable/disable phases
- Set quality gates
- Configure deployments
- Adjust AI behavior

## Documentation

- **Complete Guide**: `docs/AI_ORCHESTRATION_BLUEPRINT.md`
- **Phase Details**: `docs/AI_ORCHESTRATION_BLUEPRINT.md#phases`
- **Lessons Learned**: `docs/AI_ORCHESTRATION_BLUEPRINT.md#lessons-learned`

## Support

For issues or questions:
1. Check `docs/AI_ORCHESTRATION_BLUEPRINT.md`
2. Review logs in `.orchestration/logs/`
3. Run with `--verbose` flag

## Next Steps

1. Review configuration: `nano .orchestration.env`
2. Start your first orchestration: `./bin/orchestrate.sh --project=demo`
3. Monitor progress in real-time
4. Review generated reports in `.orchestration/reports/`

Happy orchestrating! 🚀
EOF

log_success "Quick start guide created: ORCHESTRATION_QUICK_START.md"

# Final summary
echo ""
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║                   INSTALLATION COMPLETE! ✅                         ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""
log_success "AI Orchestration System v2.0 is ready to use"
echo ""
echo "📋 Next Steps:"
echo "   1. Review configuration: ${BLUE}.orchestration.env${NC}"
echo "   2. Read quick start: ${BLUE}ORCHESTRATION_QUICK_START.md${NC}"
echo "   3. Start orchestration: ${GREEN}./bin/orchestrate.sh --project=demo${NC}"
echo ""
echo "📚 Documentation:"
echo "   - Complete Guide: ${BLUE}docs/AI_ORCHESTRATION_BLUEPRINT.md${NC}"
echo "   - API Reference: ${BLUE}docs/API-REFERENCE.md${NC}"
echo ""
echo "🔧 System Status:"
echo "   - PHP Version: ${GREEN}$PHP_VERSION${NC}"
echo "   - Project Root: ${BLUE}$PROJECT_ROOT${NC}"
echo "   - Phase Scripts: ${GREEN}$PHASE_SCRIPTS${NC}"
echo "   - Archived Docs: ${GREEN}$ARCHIVED_COUNT${NC}"
echo ""
log_info "Run ${GREEN}./bin/orchestrate.sh --help${NC} for usage information"
echo ""
