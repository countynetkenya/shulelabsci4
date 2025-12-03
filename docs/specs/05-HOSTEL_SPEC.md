# 🏨 Hostel Management Specification

**Version**: 1.0.0
**Status**: Approved
**Last Updated**: 2025-12-03

---

## Part 1: Feature Definition (The "What" & "Why")

### 1.1 Overview
The Hostel Management module handles residential facilities, room inventory, student allocations, and fee integration. It is designed to be mobile-first, allowing wardens to manage allocations via tablet and students to view status via app.

### 1.2 User Stories
- **As a Warden**, I want to view real-time occupancy stats, so that I can manage room availability efficiently.
- **As a Warden**, I want to allocate students to beds via a mobile interface, so that I can handle check-ins on the spot.
- **As a Student**, I want to view my room assignment and roommates, so that I know where to stay.
- **As a Student**, I want to submit maintenance requests, so that issues in my room are resolved.
- **As an Admin**, I want to configure hostel buildings and room capacities, so that the system reflects physical inventory.
- **As an Admin**, I want to enforce gender segregation rules, so that compliance is automatic.

### 1.3 User Workflows
1.  **Student Check-In**:
    *   Warden selects Hostel -> Room -> Bed.
    *   Searches for Student.
    *   Clicks "Allocate".
    *   System validates gender and capacity.
    *   Allocation is created.
2.  **Maintenance Request**:
    *   Student logs in to App.
    *   Navigates to "My Room".
    *   Clicks "Report Issue".
    *   Enters details and submits.
    *   Warden receives notification.

### 1.4 Acceptance Criteria
- [ ] Admin can create Hostels and Rooms with capacities.
- [ ] System prevents allocating a Male student to a Female hostel.
- [ ] System prevents allocating a student to a full room.
- [ ] Student can see their assigned room in the API response.
- [ ] Warden can see a list of all students in a specific hostel.

---

## Part 2: Technical Specs

### 2.1 Database Schema

### 2.1 Tables

#### `hostels`
Represents a physical building or block.
```sql
CREATE TABLE hostels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('boys', 'girls', 'staff', 'mixed') NOT NULL,
    warden_id INT NULL, -- Link to users table (staff)
    capacity INT DEFAULT 0, -- Total capacity (cached)
    location VARCHAR(255),
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (warden_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `hostel_rooms`
Represents a specific room within a hostel.
```sql
CREATE TABLE hostel_rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hostel_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    floor_number INT DEFAULT 0,
    capacity INT NOT NULL DEFAULT 4, -- Intended capacity
    cost_per_term DECIMAL(10, 2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_room (hostel_id, room_number)
);
```

#### `hostel_beds`
Represents a specific bed within a room.
```sql
CREATE TABLE hostel_beds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    bed_number VARCHAR(20) NOT NULL, -- e.g., "1", "A", "Top-Left"
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES hostel_rooms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bed (room_id, bed_number)
);
```

#### `hostel_allocations`
Links a student to a specific bed.
```sql
CREATE TABLE hostel_allocations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    room_id INT NOT NULL, -- Kept for faster queries
    bed_id INT NOT NULL,  -- Specific bed allocation
    academic_year VARCHAR(20),
    term VARCHAR(20),
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('active', 'vacated', 'evicted') DEFAULT 'active',
    notes TEXT,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES hostel_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (bed_id) REFERENCES hostel_beds(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### `hostel_requests`
Requests from students for rooms or changes.
```sql
CREATE TABLE hostel_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    request_type ENUM('new_allocation', 'change_room', 'maintenance', 'vacate') NOT NULL,
    details TEXT,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    admin_response TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 3. API Endpoints (Mobile First)

### 3.1 Public / Student
- `GET /api/v1/hostels/my-room` - Get current allocation details.
- `POST /api/v1/hostels/request` - Submit a request (change room, maintenance).

### 3.2 Admin / Warden
- `GET /api/v1/hostels` - List all hostels with occupancy stats.
- `GET /api/v1/hostels/{id}/rooms` - List rooms in a hostel.
- `POST /api/v1/hostels/allocate` - Assign a student to a room.
    - **Validation**: Check gender match (Boy -> Boys Hostel).
    - **Validation**: Check room capacity (Current < Max).
    - **Validation**: Check existing active allocation.
- `POST /api/v1/hostels/vacate` - Check a student out.

## 4. Business Logic Rules

1.  **Gender Segregation**: A student with gender 'Male' cannot be allocated to a 'Girls' hostel type.
2.  **Capacity Lock**: System must prevent allocation if `COUNT(active_allocations) >= room.capacity`.
3.  **Double Booking**: A student cannot have two `active` allocations simultaneously.
4.  **Fee Trigger**: (Future Integration) On allocation, an invoice item should be generated if configured.

## 5. Testing Strategy

### 5.1 Feature Tests
- `test_can_create_hostel`: Verify admin can create a building.
- `test_cannot_exceed_room_capacity`: Try to add 5th student to 4-bed room -> Expect Error.
- `test_gender_mismatch_prevention`: Try to add Boy to Girl hostel -> Expect Error.
- `test_student_can_view_own_room`: Verify API response for logged-in student.
s