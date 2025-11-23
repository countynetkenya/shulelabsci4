#!/bin/bash
################################################################################
# AI Code Review Enhancement
# Automated code quality analysis with AI-powered suggestions
################################################################################

set -e

PROJECT_ROOT="${PROJECT_ROOT:-$(pwd)}"
LOG_FILE=".orchestration/logs/ai-code-review-$(date +%Y%m%d-%H%M%S).log"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${BLUE}[AI-REVIEW]${NC} $1" | tee -a "$LOG_FILE"; }
log_success() { echo -e "${GREEN}[AI-REVIEW]${NC} $1" | tee -a "$LOG_FILE"; }
log_warning() { echo -e "${YELLOW}[AI-REVIEW]${NC} $1" | tee -a "$LOG_FILE"; }
log_error() { echo -e "${RED}[AI-REVIEW]${NC} $1" | tee -a "$LOG_FILE"; }

echo "════════════════════════════════════════════════════════════════"
echo "  AI-Powered Code Review"
echo "════════════════════════════════════════════════════════════════"

# Get changed files
log_info "Analyzing changed files..."
CHANGED_FILES=$(git diff --name-only --cached --diff-filter=ACMR "*.php" 2>/dev/null || echo "")

if [ -z "$CHANGED_FILES" ]; then
    log_warning "No PHP files staged for commit"
    exit 0
fi

FILE_COUNT=$(echo "$CHANGED_FILES" | wc -l)
log_info "Found $FILE_COUNT changed PHP file(s)"

# Initialize results
TOTAL_ISSUES=0
CRITICAL_ISSUES=0
SUGGESTIONS=()

# Analyze each file
while IFS= read -r file; do
    if [ ! -f "$file" ]; then
        continue
    fi
    
    log_info "Reviewing: $file"
    
    # Check complexity
    COMPLEXITY=$(php -r "
        \$code = file_get_contents('$file');
        \$functions = preg_match_all('/function\s+\w+\s*\(/', \$code, \$matches);
        \$ifs = preg_match_all('/(if|else|elseif|for|foreach|while|switch|case)\s*\(/', \$code, \$matches);
        echo \$ifs;
    " 2>/dev/null || echo "0")
    
    if [ "$COMPLEXITY" -gt 50 ]; then
        log_warning "  ⚠️  High complexity detected ($COMPLEXITY decision points)"
        SUGGESTIONS+=("$file: Consider refactoring - complexity score: $COMPLEXITY")
        ((TOTAL_ISSUES++))
    fi
    
    # Check file size
    LINES=$(wc -l < "$file" 2>/dev/null || echo "0")
    if [ "$LINES" -gt 500 ]; then
        log_warning "  ⚠️  Large file detected ($LINES lines)"
        SUGGESTIONS+=("$file: Consider splitting - $LINES lines")
        ((TOTAL_ISSUES++))
    fi
    
    # Check for common issues
    if grep -q "var_dump\|print_r\|dd(" "$file" 2>/dev/null; then
        log_error "  ❌ Debug code detected (var_dump/print_r/dd)"
        SUGGESTIONS+=("$file: Remove debug statements")
        ((CRITICAL_ISSUES++))
        ((TOTAL_ISSUES++))
    fi
    
    if grep -q "TODO\|FIXME\|HACK" "$file" 2>/dev/null; then
        log_warning "  ⚠️  TODO/FIXME comments found"
        SUGGESTIONS+=("$file: Address TODO/FIXME comments")
        ((TOTAL_ISSUES++))
    fi
    
    # Check for SQL injection risks
    if grep -qE "\\\$this->db->query\(.*\\\$|query\(.*\..*\\\$" "$file" 2>/dev/null; then
        log_error "  ❌ Potential SQL injection risk"
        SUGGESTIONS+=("$file: Use query bindings instead of string concatenation")
        ((CRITICAL_ISSUES++))
        ((TOTAL_ISSUES++))
    fi
    
    # Check for missing type hints (PHP 8+)
    if ! grep -q "declare(strict_types=1)" "$file" 2>/dev/null; then
        log_warning "  ⚠️  Missing strict_types declaration"
        SUGGESTIONS+=("$file: Add declare(strict_types=1) at top of file")
        ((TOTAL_ISSUES++))
    fi
    
    # Check for proper error handling
    FUNC_COUNT=$(grep -c "function " "$file" 2>/dev/null || echo "0")
    TRY_COUNT=$(grep -c "try {" "$file" 2>/dev/null || echo "0")
    
    if [ "$FUNC_COUNT" -gt 5 ] && [ "$TRY_COUNT" -eq 0 ]; then
        log_warning "  ⚠️  No try-catch blocks found"
        SUGGESTIONS+=("$file: Consider adding error handling")
        ((TOTAL_ISSUES++))
    fi
    
    log_success "  ✓ Review complete"
    
done <<< "$CHANGED_FILES"

# Generate report
echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  Review Summary"
echo "════════════════════════════════════════════════════════════════"
echo "Files analyzed:     $FILE_COUNT"
echo "Total issues:       $TOTAL_ISSUES"
echo "Critical issues:    $CRITICAL_ISSUES"
echo ""

if [ "$CRITICAL_ISSUES" -gt 0 ]; then
    log_error "CRITICAL ISSUES DETECTED - Fix before committing!"
    echo ""
    echo "Suggestions:"
    for suggestion in "${SUGGESTIONS[@]}"; do
        echo "  • $suggestion"
    done
    exit 1
fi

if [ "$TOTAL_ISSUES" -gt 0 ]; then
    log_warning "Issues detected - review recommended"
    echo ""
    echo "Suggestions:"
    for suggestion in "${SUGGESTIONS[@]}"; do
        echo "  • $suggestion"
    done
    echo ""
    read -p "Continue with commit? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

log_success "Code review passed! ✓"
exit 0
