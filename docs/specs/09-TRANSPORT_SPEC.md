# 🚌 Transport Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Transport module manages school transportation including routes, vehicles, drivers, stops, and student assignments. It integrates with Finance for transport fee billing and Threads for parent notifications. The module is designed mobile-first, enabling drivers to manage trips via tablet and parents to track buses via app.

### 1.2 User Stories

- As a **Transport Manager**, I want to define routes with multiple stops so students can be assigned to pickup points.
- As a **Parent**, I want to track my child's bus location in real-time and receive pickup/drop-off notifications.
- As an **Admin**, I want transport fees automatically added to student invoices based on their route assignment.
- As a **Driver**, I want to mark student attendance on the bus and report delays.
- As a **Student**, I want to view my assigned route, pickup time, and stop location.
- As a **Finance Officer**, I want to generate transport fee reports by route and term.

### 1.3 User Workflows

1. **Route Setup**:
   - Admin creates a new route with name and description.
   - Admin adds stops with pickup/dropoff times and GPS coordinates.
   - Admin assigns a vehicle and driver to the route.
   - Admin sets the monthly fee for the route.

2. **Student Assignment**:
   - Admin selects a student from the student list.
   - Admin chooses the route and pickup/dropoff stop.
   - System calculates the monthly fee (route fee + stop adjustment).
   - Assignment is saved and fee integration triggers.

3. **Daily Operations**:
   - Driver starts the morning/evening trip from the app.
   - At each stop, driver marks students as picked up or absent.
   - Parents receive notifications when their child boards.
   - Driver completes the trip and logs any notes.

4. **Fee Integration**:
   - At billing cycle, system fetches active transport assignments.
   - Monthly transport fees are added to student invoices.
   - Parents view transport fee on their invoice and pay.

### 1.4 Acceptance Criteria

- [ ] Admin can create, edit, and delete vehicles with registration details.
- [ ] Admin can create routes with multiple stops including GPS coordinates.
- [ ] Admin can assign/unassign drivers and vehicles to routes.
- [ ] Admin can assign students to routes and specific stops.
- [ ] System calculates transport fees based on route and stop assignments.
- [ ] Driver can start a trip and mark student attendance.
- [ ] Parents receive notifications on pickup/dropoff events.
- [ ] GPS tracking logs are recorded during active trips.
- [ ] Transport fees integrate with Finance module invoicing.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `transport_vehicles`
Represents a vehicle in the transport fleet.
```sql
CREATE TABLE transport_vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    registration_number VARCHAR(20) NOT NULL,
    vehicle_type ENUM('bus', 'van', 'car') DEFAULT 'bus',
    capacity INT NOT NULL,
    make VARCHAR(50),
    model VARCHAR(50),
    year INT,
    insurance_expiry DATE,
    fitness_expiry DATE,
    status ENUM('active', 'maintenance', 'retired') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_registration (school_id, registration_number),
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
);
```

#### `transport_drivers`
Represents a driver assigned to transport duty.
```sql
CREATE TABLE transport_drivers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    license_expiry DATE,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `transport_routes`
Represents a transport route with assigned vehicle and driver.
```sql
CREATE TABLE transport_routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    description TEXT,
    vehicle_id INT,
    driver_id INT,
    route_type ENUM('morning', 'evening', 'both') DEFAULT 'both',
    monthly_fee DECIMAL(10,2) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES transport_vehicles(id) ON DELETE SET NULL,
    FOREIGN KEY (driver_id) REFERENCES transport_drivers(id) ON DELETE SET NULL
);
```

#### `transport_stops`
Represents a stop along a route.
```sql
CREATE TABLE transport_stops (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    pickup_time TIME,
    dropoff_time TIME,
    stop_order INT NOT NULL,
    fee_adjustment DECIMAL(10,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES transport_routes(id) ON DELETE CASCADE
);
```

#### `transport_assignments`
Links a student to a route and stop.
```sql
CREATE TABLE transport_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    route_id INT NOT NULL,
    stop_id INT NOT NULL,
    assignment_type ENUM('pickup', 'dropoff', 'both') DEFAULT 'both',
    monthly_fee DECIMAL(10,2) NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_student_route (student_id, route_id, effective_from),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES transport_routes(id) ON DELETE CASCADE,
    FOREIGN KEY (stop_id) REFERENCES transport_stops(id) ON DELETE CASCADE
);
```

#### `transport_trips`
Daily trip log for a route.
```sql
CREATE TABLE transport_trips (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_id INT NOT NULL,
    trip_date DATE NOT NULL,
    trip_type ENUM('morning', 'evening') NOT NULL,
    vehicle_id INT NOT NULL,
    driver_id INT NOT NULL,
    started_at DATETIME,
    completed_at DATETIME,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES transport_routes(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES transport_vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES transport_drivers(id) ON DELETE CASCADE
);
```

#### `transport_attendance`
Per-trip student attendance record.
```sql
CREATE TABLE transport_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trip_id INT NOT NULL,
    student_id INT NOT NULL,
    stop_id INT NOT NULL,
    status ENUM('picked', 'dropped', 'absent', 'parent_pickup') DEFAULT 'picked',
    marked_at DATETIME,
    marked_by INT,
    notes TEXT,
    FOREIGN KEY (trip_id) REFERENCES transport_trips(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (stop_id) REFERENCES transport_stops(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_trip_student (trip_id, student_id)
);
```

#### `transport_gps_logs`
Real-time GPS tracking data.
```sql
CREATE TABLE transport_gps_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    trip_id INT NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    speed DECIMAL(5,2),
    heading INT,
    recorded_at DATETIME NOT NULL,
    INDEX idx_trip_time (trip_id, recorded_at),
    FOREIGN KEY (trip_id) REFERENCES transport_trips(id) ON DELETE CASCADE
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET | `/api/v1/transport/vehicles` | List all vehicles | Staff |
| POST | `/api/v1/transport/vehicles` | Create a new vehicle | Admin |
| GET | `/api/v1/transport/vehicles/{id}` | Get vehicle details | Staff |
| PUT | `/api/v1/transport/vehicles/{id}` | Update vehicle | Admin |
| DELETE | `/api/v1/transport/vehicles/{id}` | Delete vehicle | Admin |
| GET | `/api/v1/transport/drivers` | List all drivers | Staff |
| POST | `/api/v1/transport/drivers` | Create a new driver | Admin |
| GET | `/api/v1/transport/drivers/{id}` | Get driver details | Staff |
| PUT | `/api/v1/transport/drivers/{id}` | Update driver | Admin |
| GET | `/api/v1/transport/routes` | List all routes | Staff |
| POST | `/api/v1/transport/routes` | Create a new route | Admin |
| GET | `/api/v1/transport/routes/{id}` | Get route details | Staff |
| PUT | `/api/v1/transport/routes/{id}` | Update route | Admin |
| GET | `/api/v1/transport/routes/{id}/stops` | Get stops for a route | Staff |
| POST | `/api/v1/transport/routes/{id}/stops` | Add stop to route | Admin |
| PUT | `/api/v1/transport/stops/{id}` | Update stop | Admin |
| DELETE | `/api/v1/transport/stops/{id}` | Delete stop | Admin |
| GET | `/api/v1/transport/students/{id}/assignment` | Get student's transport assignment | Staff/Parent |
| POST | `/api/v1/transport/assignments` | Assign student to route | Admin |
| PUT | `/api/v1/transport/assignments/{id}` | Update assignment | Admin |
| DELETE | `/api/v1/transport/assignments/{id}` | Remove assignment | Admin |
| GET | `/api/v1/transport/trips` | List trips (with date filter) | Staff |
| POST | `/api/v1/transport/trips` | Create/schedule a trip | Admin |
| POST | `/api/v1/transport/trips/{id}/start` | Start a trip | Driver |
| POST | `/api/v1/transport/trips/{id}/complete` | Complete a trip | Driver |
| POST | `/api/v1/transport/trips/{id}/attendance` | Mark attendance | Driver |
| GET | `/api/v1/transport/trips/{id}/track` | Get live GPS location | Parent |
| GET | `/api/v1/transport/my-assignment` | Get current user's transport (Student) | Student |

### 2.3 Web Interface (Views & Controllers)

- **Controller**: `App\Modules\Transport\Controllers\Web\TransportDashboardController`
- **Views** (in `app/Views/modules/transport/`):
  - `dashboard.php`: Transport overview with stats
  - `vehicles/index.php`: Vehicle list (DataTables)
  - `vehicles/form.php`: Create/Edit vehicle form
  - `drivers/index.php`: Driver list
  - `drivers/form.php`: Create/Edit driver form
  - `routes/index.php`: Route list with stop counts
  - `routes/form.php`: Create/Edit route form
  - `routes/stops.php`: Manage stops for a route
  - `assignments/index.php`: Student assignment list
  - `assignments/form.php`: Assign student form
  - `trips/index.php`: Trip log list
  - `trips/tracking.php`: Live tracking map view

### 2.4 Models & Validation

#### VehicleModel
- **registration_number**: required, max_length[20], is_unique[transport_vehicles.registration_number,id,{id}]
- **vehicle_type**: required, in_list[bus,van,car]
- **capacity**: required, integer, greater_than[0]
- **insurance_expiry**: permit_empty, valid_date
- **fitness_expiry**: permit_empty, valid_date

#### DriverModel
- **name**: required, max_length[100]
- **phone**: required, max_length[20]
- **license_number**: required, max_length[50]
- **license_expiry**: permit_empty, valid_date

#### RouteModel
- **name**: required, max_length[100]
- **route_type**: required, in_list[morning,evening,both]
- **monthly_fee**: required, decimal

#### StopModel
- **name**: required, max_length[100]
- **stop_order**: required, integer, greater_than[0]
- **latitude**: permit_empty, decimal
- **longitude**: permit_empty, decimal

#### AssignmentModel
- **student_id**: required, integer
- **route_id**: required, integer
- **stop_id**: required, integer
- **assignment_type**: required, in_list[pickup,dropoff,both]
- **monthly_fee**: required, decimal
- **effective_from**: required, valid_date

### 2.5 Integration Points

- **Finance Module**: Transport fees added to student invoices via `TransportFeeService`. When an assignment is created/updated, the service calculates the fee and creates an invoice item.
- **Threads Module**: Parent notifications for pickup/dropoff events dispatched via event system (`Events::trigger('transport.student.picked', $data)`).
- **Learning Module**: Student data retrieved from Learning module models for assignment lookups.
- **Reports Module**: Transport embedded report for Student entity view showing assignment details.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Tenant Isolation
- All database queries MUST be scoped by `school_id` to ensure multi-tenant data isolation.
- Use model filters or traits to enforce school_id filtering automatically.

### 3.2 GPS Data Retention
- GPS logs (`transport_gps_logs`) should be auto-purged after 30 days to manage storage.
- Implement a scheduled task (cron job) to delete old GPS records.

### 3.3 Fee Calculation
- Use database transactions when creating/updating assignments and their associated invoice items.
- Fee = `route.monthly_fee + stop.fee_adjustment`
- Ensure atomicity to prevent orphaned invoice items.

### 3.4 Real-time Tracking
- Use WebSocket or long-polling for live tracking.
- Implement rate limiting (max 1 GPS log per 10 seconds per trip).
- Consider using Redis pub/sub for scalable real-time updates.

### 3.5 Driver Authentication
- Drivers should authenticate via mobile app with PIN or biometric.
- Trip start/complete actions require driver's authenticated session.

---

## Part 4: Embedded Reports for Entity Views
*Target Audience: Frontend Developers, Product Owners*

### 4.1 Student View - Transport Tab

When viewing a student's profile, display a "Transport" tab with:

| Field | Description |
|:------|:------------|
| Current Route | Name and code of assigned route |
| Pickup Stop | Name and address of pickup stop |
| Dropoff Stop | Name and address of dropoff stop |
| Pickup Time | Scheduled pickup time |
| Dropoff Time | Scheduled dropoff time |
| Monthly Fee | Current transport fee |
| Driver | Name and phone of assigned driver |
| Vehicle | Registration number and type |
| Attendance (Last 30 Days) | Table showing date, trip type, status |

### 4.2 Route View - Students Tab

When viewing a route's details, display assigned students:

| Field | Description |
|:------|:------------|
| Student Name | Full name with link to profile |
| Class | Student's current class |
| Stop | Assigned pickup/dropoff stop |
| Fee | Monthly fee for this student |
| Status | Active/Inactive |

---

## Part 5: Module Structure

```
app/Modules/Transport/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Controllers/
│   ├── Api/
│   │   ├── VehicleController.php
│   │   ├── DriverController.php
│   │   ├── RouteController.php
│   │   ├── StopController.php
│   │   ├── AssignmentController.php
│   │   ├── TripController.php
│   │   └── TrackingController.php
│   └── Web/
│       └── TransportDashboardController.php
├── Models/
│   ├── VehicleModel.php
│   ├── DriverModel.php
│   ├── RouteModel.php
│   ├── StopModel.php
│   ├── AssignmentModel.php
│   ├── TripModel.php
│   ├── AttendanceModel.php
│   └── GpsLogModel.php
├── Services/
│   ├── RouteService.php
│   ├── AssignmentService.php
│   ├── TripService.php
│   ├── TrackingService.php
│   └── TransportFeeService.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateTransportTables.php
└── Tests/
    └── Feature/
        └── TransportTest.php
```

---

## Part 6: Development Checklist

- [ ] **Design**: Review and approve this specification document.
- [ ] **Tests**: Write failing feature tests (TDD) for core functionality.
- [ ] **Scaffold**: Generate Controllers, Models, and Migrations using `spark`.
- [ ] **Database**: Run migrations and verify schema in all environments.
- [ ] **API**: Implement API endpoints with proper validation and authorization.
- [ ] **Web**: Build web interface views and controllers.
- [ ] **Integration**: Connect to Finance module for fee billing.
- [ ] **Notifications**: Implement parent notifications via Threads module.
- [ ] **GPS Tracking**: Implement real-time tracking with rate limiting.
- [ ] **Review**: Code review and merge to main branch.

---

## Part 7: Testing Strategy

### 7.1 Feature Tests

- `test_admin_can_create_vehicle`: Verify admin can create a vehicle with valid data.
- `test_admin_can_create_route_with_stops`: Verify route creation with multiple stops.
- `test_admin_can_assign_student_to_route`: Verify student assignment and fee calculation.
- `test_driver_can_start_trip`: Verify driver can start a scheduled trip.
- `test_driver_can_mark_attendance`: Verify attendance marking for students.
- `test_parent_can_view_child_assignment`: Verify parent API access to child's transport.
- `test_gps_log_recorded_during_trip`: Verify GPS logging during active trips.
- `test_fee_integration_creates_invoice_item`: Verify Finance module receives fee data.
- `test_school_id_isolation`: Verify data is isolated between schools.

### 7.2 Unit Tests

- `RouteServiceTest`: Test fee calculation logic.
- `AssignmentServiceTest`: Test assignment validation rules.
- `TrackingServiceTest`: Test GPS data processing and rate limiting.
