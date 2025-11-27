#!/bin/bash
# save as: scripts/check_database_errors.sh

echo "=== Checking for database errors in logs ===" 

# Look for table not found errors
grep -i "no such table\|table.*doesn't exist\|unknown table" writable/logs/log-*.log 2>/dev/null

# Look for specific ci4_ reference errors
grep -i "ci4_users\|ci4_roles\|ci4_user_roles" writable/logs/log-*.log 2>/dev/null

# Look for general SQL errors
grep -i "database error\|query error\|SQL syntax" writable/logs/log-*.log 2>/dev/null

# Count errors
ERROR_COUNT=$(grep -c -i "no such table\|table.*doesn't exist\|ci4_" writable/logs/log-*.log 2>/dev/null | awk -F: '{sum+=$2} END {print sum}')

if [ "$ERROR_COUNT" -gt 0 ]; then
    echo "❌ FAILED: Found $ERROR_COUNT database errors in logs"
    exit 1
else
    echo "✅ PASSED: No database errors found"
    exit 0
fi
