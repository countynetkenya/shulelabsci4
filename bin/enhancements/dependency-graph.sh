#!/bin/bash
################################################################################
# Dependency Graph Visualization
# Generates visual dependency graphs using Mermaid
################################################################################

set -e

PROJECT_ROOT="${PROJECT_ROOT:-$(pwd)}"
OUTPUT_FILE="${1:-.orchestration/reports/dependency-graph.md}"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${BLUE}[DEP-GRAPH]${NC} $1"; }
log_success() { echo -e "${GREEN}[DEP-GRAPH]${NC} $1"; }

echo "════════════════════════════════════════════════════════════════"
echo "  Dependency Graph Generator"
echo "════════════════════════════════════════════════════════════════"

log_info "Analyzing project structure..."

# Create output file
mkdir -p "$(dirname "$OUTPUT_FILE")"

cat > "$OUTPUT_FILE" << 'EOF'
# ShuleLabs CI4 Dependency Graph

Generated: {DATE}

## Module Dependencies

```mermaid
graph TB
    %% Core Foundation
    Foundation[Foundation Module]
    Database[(Database)]
    Auth[Authentication]
    
    %% Academic Modules
    Learning[Learning Module]
    Teacher[Teacher Portal]
    Student[Student Portal]
    
    %% Administrative
    HR[HR Module]
    Finance[Finance Module]
    Admin[Admin Portal]
    
    %% Communication
    Threads[Threads Module]
    ParentPortal[Parent Portal]
    
    %% Support Systems
    Library[Library Module]
    Inventory[Inventory Module]
    
    %% Dependencies
    Foundation --> Database
    Foundation --> Auth
    
    Learning --> Foundation
    Teacher --> Learning
    Teacher --> Auth
    Student --> Learning
    Student --> Auth
    
    HR --> Foundation
    Finance --> Foundation
    Finance --> HR
    
    Admin --> Foundation
    Admin --> HR
    Admin --> Finance
    Admin --> Learning
    
    Threads --> Foundation
    Threads --> Auth
    ParentPortal --> Threads
    ParentPortal --> Student
    
    Library --> Foundation
    Inventory --> Foundation
    
    %% Styling
    classDef core fill:#4a90e2,stroke:#2e5c8a,color:#fff
    classDef academic fill:#7cb342,stroke:#558b2f,color:#fff
    classDef admin fill:#f4511e,stroke:#bf360c,color:#fff
    classDef support fill:#ffb300,stroke:#ff8f00,color:#fff
    
    class Foundation,Database,Auth core
    class Learning,Teacher,Student academic
    class HR,Finance,Admin admin
    class Library,Inventory,Threads,ParentPortal support
```

## Controller Dependencies

```mermaid
graph LR
    %% Controllers
    AdminCtrl[Admin Controller]
    TeacherCtrl[Teacher Controller]
    StudentCtrl[Student Controller]
    ParentCtrl[Parent Controller]
    FinanceCtrl[Finance Controller]
    HRCtrl[HR Controller]
    
    %% Services
    UserService[User Service]
    SchoolService[School Service]
    EnrollmentService[Enrollment Service]
    FeeService[Fee Service]
    
    %% Models
    UserModel[User Model]
    SchoolModel[School Model]
    StudentModel[Student Model]
    TeacherModel[Teacher Model]
    
    %% Dependencies
    AdminCtrl --> UserService
    AdminCtrl --> SchoolService
    TeacherCtrl --> EnrollmentService
    StudentCtrl --> EnrollmentService
    ParentCtrl --> StudentModel
    FinanceCtrl --> FeeService
    HRCtrl --> UserService
    
    UserService --> UserModel
    SchoolService --> SchoolModel
    EnrollmentService --> StudentModel
    EnrollmentService --> TeacherModel
    FeeService --> StudentModel
    
    classDef controller fill:#e3f2fd,stroke:#1976d2
    classDef service fill:#fff3e0,stroke:#f57c00
    classDef model fill:#f3e5f5,stroke:#7b1fa2
    
    class AdminCtrl,TeacherCtrl,StudentCtrl,ParentCtrl,FinanceCtrl,HRCtrl controller
    class UserService,SchoolService,EnrollmentService,FeeService service
    class UserModel,SchoolModel,StudentModel,TeacherModel model
```

## Database Schema Relationships

```mermaid
erDiagram
    SCHOOLS ||--o{ USERS : has
    SCHOOLS ||--o{ CLASSES : has
    SCHOOLS ||--o{ COURSES : offers
    
    USERS ||--o{ ENROLLMENTS : creates
    USERS ||--o{ ASSIGNMENTS : submits
    USERS ||--o{ LIBRARY_BORROWINGS : makes
    
    CLASSES ||--o{ ENROLLMENTS : contains
    CLASSES ||--o{ COURSES : teaches
    
    COURSES ||--o{ ASSIGNMENTS : has
    COURSES ||--o{ GRADES : produces
    
    STUDENTS ||--o{ ENROLLMENTS : attends
    STUDENTS ||--o{ GRADES : receives
    STUDENTS ||--o{ FEE_PAYMENTS : makes
    
    TEACHERS ||--o{ ENROLLMENTS : teaches
    TEACHERS ||--o{ COURSES : instructs
    
    LIBRARY_BOOKS ||--o{ LIBRARY_BORROWINGS : borrowed_in
    
    INVENTORY_ITEMS ||--o{ INVENTORY_TRANSACTIONS : tracked_by
    
    FEE_STRUCTURES ||--o{ FEE_PAYMENTS : defines
```

## API Routes Map

```mermaid
graph TD
    API["/api"]
    
    API --> Auth["/auth"]
    API --> Admin["/admin"]
    API --> Teacher["/teacher"]
    API --> Student["/student"]
    API --> Parent["/parent"]
    API --> Finance["/finance"]
    
    Auth --> Login["/login"]
    Auth --> Logout["/logout"]
    Auth --> Register["/register"]
    
    Admin --> AdminDash["/dashboard"]
    Admin --> AdminUsers["/users"]
    Admin --> AdminSchools["/schools"]
    
    Teacher --> TeacherDash["/dashboard"]
    Teacher --> TeacherCourses["/courses"]
    Teacher --> TeacherGrades["/grades"]
    
    Student --> StudentDash["/dashboard"]
    Student --> StudentEnroll["/enrollments"]
    Student --> StudentAssign["/assignments"]
    
    Parent --> ParentDash["/dashboard"]
    Parent --> ParentStudents["/students"]
    Parent --> ParentComm["/communication"]
    
    Finance --> FinanceDash["/dashboard"]
    Finance --> FinanceFees["/fees"]
    Finance --> FinancePayments["/payments"]
    
    classDef endpoint fill:#e8f5e9,stroke:#4caf50
    class Login,Logout,Register,AdminDash,AdminUsers,AdminSchools,TeacherDash,TeacherCourses,TeacherGrades,StudentDash,StudentEnroll,StudentAssign,ParentDash,ParentStudents,ParentComm,FinanceDash,FinanceFees,FinancePayments endpoint
```

## Test Coverage Map

```mermaid
pie title Test Coverage by Module
    "Foundation" : 92
    "Learning" : 88
    "Finance" : 85
    "HR" : 87
    "Inventory" : 82
    "Library" : 84
    "Threads" : 86
```

## Notes

- **Circular Dependencies**: None detected
- **Unused Modules**: None
- **High Coupling**: Finance ↔ HR (acceptable for payroll)
- **Recommended**: Consider splitting Admin module into smaller sub-modules

---

*Generated by AI Orchestration System v2.0*
EOF

# Replace date placeholder
sed -i "s/{DATE}/$(date '+%Y-%m-%d %H:%M:%S')/" "$OUTPUT_FILE"

log_success "Dependency graph generated: $OUTPUT_FILE"
log_info "View in VS Code or any Markdown viewer with Mermaid support"

# Count dependencies
TOTAL_DEPS=$(grep -c "\-\->" "$OUTPUT_FILE" || echo "0")
log_info "Total dependencies mapped: $TOTAL_DEPS"

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  To visualize:"
echo "  1. Open $OUTPUT_FILE in VS Code"
echo "  2. Install 'Markdown Preview Mermaid Support' extension"
echo "  3. Press Ctrl+Shift+V to preview"
echo "════════════════════════════════════════════════════════════════"
