# 📱 Mobile Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Mobile module is the "App Engine" of ShuleLabs. It provides mobile-first REST API design patterns, JWT authentication for native apps, offline synchronization capabilities, push notification integration, and optimized payload sizes. This module ensures that the Student, Parent, and Teacher mobile apps have the best possible experience.

### 1.2 User Stories

- **As a Parent**, I want to receive push notifications when my child is marked absent, so that I'm informed immediately.
- **As a Student**, I want to view my timetable offline, so that I can check it without internet.
- **As a Teacher**, I want to mark attendance on my tablet even without WiFi, so that it syncs when connected.
- **As a Developer**, I want optimized API responses for mobile, so that data transfer is minimal.
- **As a User**, I want to stay logged in on my phone, so that I don't have to log in every time.
- **As an Admin**, I want to manage which devices are registered, so that I can revoke access if needed.

### 1.3 User Workflows

1. **Mobile Authentication**:
   - User enters credentials in app.
   - App sends to `/api/v1/auth/login`.
   - Server validates and returns JWT tokens.
   - App stores tokens securely.
   - Subsequent requests include Bearer token.
   - Refresh token used when access token expires.

2. **Offline Data Sync**:
   - App downloads sync snapshot on login.
   - User works offline (views data, marks attendance).
   - Actions queued locally.
   - When online, app syncs queued actions.
   - Server processes and responds with updates.
   - App updates local cache.

3. **Push Notification**:
   - User grants notification permission.
   - App registers FCM token with server.
   - Server event triggers (absence marked).
   - Server sends push via FCM.
   - User receives notification.
   - Tapping notification opens relevant screen.

4. **Device Management**:
   - User logs into new device.
   - Server registers device with token.
   - User views logged-in devices.
   - User can revoke access to device.
   - Server invalidates tokens for that device.

### 1.4 Acceptance Criteria

- [ ] JWT authentication with refresh tokens.
- [ ] Token expiry and refresh flow working.
- [ ] Sync snapshots generated efficiently.
- [ ] Offline queue processes correctly.
- [ ] Push notifications delivered via FCM.
- [ ] Device registration and management.
- [ ] API responses optimized for mobile.
- [ ] Rate limiting for mobile endpoints.
- [ ] All data scoped by school_id and user.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `mobile_devices`
Registered mobile devices.
```sql
CREATE TABLE mobile_devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    device_id VARCHAR(255) NOT NULL,
    device_type ENUM('ios', 'android', 'web') NOT NULL,
    device_name VARCHAR(255),
    device_model VARCHAR(100),
    os_version VARCHAR(50),
    app_version VARCHAR(20),
    push_token VARCHAR(500),
    push_provider ENUM('fcm', 'apns') DEFAULT 'fcm',
    is_active BOOLEAN DEFAULT TRUE,
    last_active_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_device (user_id, device_id),
    INDEX idx_push_token (push_token(100))
);
```

#### `refresh_tokens`
JWT refresh tokens.
```sql
CREATE TABLE refresh_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    device_id VARCHAR(255),
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    is_revoked BOOLEAN DEFAULT FALSE,
    revoked_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_token (token_hash),
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
);
```

#### `sync_snapshots`
Offline sync data packages.
```sql
CREATE TABLE sync_snapshots (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    school_id INT NOT NULL,
    snapshot_type VARCHAR(50) NOT NULL,
    snapshot_version INT NOT NULL,
    data_hash VARCHAR(64) NOT NULL,
    data_compressed LONGBLOB,
    record_count INT DEFAULT 0,
    size_bytes INT,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, snapshot_type),
    INDEX idx_expires (expires_at)
);
```

#### `offline_queue`
Pending offline actions.
```sql
CREATE TABLE offline_queue (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    device_id VARCHAR(255) NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT,
    payload JSON NOT NULL,
    client_timestamp DATETIME NOT NULL,
    server_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processed', 'conflict', 'failed') DEFAULT 'pending',
    conflict_resolution JSON,
    error_message TEXT,
    processed_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_device (device_id)
);
```

#### `api_rate_limits`
Rate limiting tracking.
```sql
CREATE TABLE api_rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    requests INT DEFAULT 1,
    window_start DATETIME NOT NULL,
    UNIQUE KEY uk_identifier_endpoint (identifier, endpoint),
    INDEX idx_window (window_start)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Authentication** |
| POST | `/api/v1/auth/login` | Login with credentials | Public |
| POST | `/api/v1/auth/refresh` | Refresh access token | Auth |
| POST | `/api/v1/auth/logout` | Logout and revoke token | Auth |
| POST | `/api/v1/auth/logout-all` | Logout all devices | Auth |
| **Devices** |
| GET | `/api/v1/mobile/devices` | List user's devices | Auth |
| POST | `/api/v1/mobile/devices/register` | Register device | Auth |
| DELETE | `/api/v1/mobile/devices/{id}` | Revoke device | Auth |
| PUT | `/api/v1/mobile/devices/{id}/push-token` | Update push token | Auth |
| **Sync** |
| GET | `/api/v1/mobile/sync/snapshot` | Get full snapshot | Auth |
| GET | `/api/v1/mobile/sync/delta` | Get changes since version | Auth |
| POST | `/api/v1/mobile/sync/push` | Push offline changes | Auth |
| GET | `/api/v1/mobile/sync/status` | Check sync status | Auth |
| **App-Specific** |
| GET | `/api/v1/mobile/student/dashboard` | Student app dashboard | Student |
| GET | `/api/v1/mobile/parent/dashboard` | Parent app dashboard | Parent |
| GET | `/api/v1/mobile/teacher/dashboard` | Teacher app dashboard | Teacher |
| GET | `/api/v1/mobile/student/timetable` | Optimized timetable | Student |
| GET | `/api/v1/mobile/parent/children` | Children summary | Parent |
| POST | `/api/v1/mobile/teacher/attendance` | Submit attendance | Teacher |

### 2.3 Module Structure

```
app/Modules/Mobile/
├── Config/
│   ├── Routes.php
│   ├── Services.php
│   └── Jwt.php
├── Controllers/
│   ├── Api/
│   │   ├── AuthController.php
│   │   ├── DeviceController.php
│   │   ├── SyncController.php
│   │   ├── StudentAppController.php
│   │   ├── ParentAppController.php
│   │   └── TeacherAppController.php
│   └── Web/
│       └── MobileAdminController.php
├── Models/
│   ├── MobileDeviceModel.php
│   ├── RefreshTokenModel.php
│   ├── SyncSnapshotModel.php
│   ├── OfflineQueueModel.php
│   └── ApiRateLimitModel.php
├── Services/
│   ├── JwtService.php
│   ├── RefreshTokenService.php
│   ├── DeviceRegistrationService.php
│   ├── SyncService.php
│   ├── SnapshotGeneratorService.php
│   ├── DeltaSyncService.php
│   ├── OfflineQueueProcessor.php
│   ├── ConflictResolver.php
│   └── RateLimiterService.php
├── Middleware/
│   ├── JwtAuthMiddleware.php
│   ├── RateLimitMiddleware.php
│   └── DeviceTrackingMiddleware.php
├── Transformers/
│   ├── StudentDashboardTransformer.php
│   ├── ParentDashboardTransformer.php
│   ├── TeacherDashboardTransformer.php
│   └── CompactTimetableTransformer.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateMobileTables.php
├── Views/
│   └── admin/
│       └── devices.php
└── Tests/
    ├── Unit/
    │   └── JwtServiceTest.php
    └── Feature/
        └── MobileAuthTest.php
```

### 2.4 JWT Token Structure

#### Access Token (15 min expiry)
```json
{
  "iss": "shuleLabs",
  "sub": 12345,
  "iat": 1700000000,
  "exp": 1700000900,
  "school_id": 1,
  "roles": ["parent"],
  "device_id": "abc123"
}
```

#### Refresh Token (30 day expiry)
- Stored in database as hashed value
- Rotated on each refresh (old token invalidated)
- One active refresh token per device

### 2.5 Sync Snapshot Types

| Type | Contents | Size Estimate | Refresh |
|:-----|:---------|:--------------|:--------|
| `student_core` | Profile, class, fees summary | 5KB | On change |
| `student_timetable` | Weekly timetable | 3KB | Weekly |
| `student_grades` | Current term grades | 10KB | On entry |
| `parent_children` | All children summaries | 15KB | On change |
| `parent_fees` | Fee balances, invoices | 20KB | On payment |
| `teacher_classes` | Allocated classes | 5KB | On term |
| `teacher_students` | Class student lists | 50KB | On change |

### 2.6 Integration Points

- **All Modules**: Mobile API endpoints for each feature.
- **Integrations Module**: Push notifications via FCM/APNs.
- **Learning Module**: Attendance offline sync.
- **Finance Module**: Fee viewing and payment.
- **Threads Module**: Message delivery via push.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Token Security
- Access tokens short-lived (15 min).
- Refresh tokens rotated on use.
- Tokens revoked on logout.
- Device-bound tokens prevent replay.

### 3.2 Rate Limiting
- Per-user and per-endpoint limits.
- Burst allowance with sliding window.
- 429 response with retry-after header.
- Whitelist for critical endpoints.

### 3.3 Offline Conflict Resolution
- Client timestamp for ordering.
- Last-write-wins for simple conflicts.
- Manual resolution for complex conflicts.
- Conflict history preserved.

### 3.4 Data Minimization
- Response transformers strip unnecessary fields.
- Pagination enforced.
- Compression for large payloads.
- Delta sync reduces transfer.

### 3.5 Secure Storage Guidance
- iOS: Keychain for tokens.
- Android: EncryptedSharedPreferences.
- Biometric unlock for sensitive data.

---

## Part 4: Test Data Strategy

### 4.1 Seeding Strategy
- Sample devices for test users.
- Refresh tokens for testing.
- Sync snapshots generated.

### 4.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Login and receive tokens | Access + Refresh tokens |
| Refresh expired access token | New access token |
| Offline attendance sync | Processed correctly |
| Rate limit exceeded | 429 with retry-after |
| Revoke device | All tokens invalidated |

---

## Part 5: Development Checklist

- [ ] **JWT**: Token generation and validation.
- [ ] **Auth**: Login, refresh, logout flows.
- [ ] **Devices**: Registration and management.
- [ ] **Sync**: Snapshot generation.
- [ ] **Sync**: Delta updates.
- [ ] **Offline**: Queue processing.
- [ ] **Rate Limiting**: Implementation.
- [ ] **Dashboards**: Optimized app endpoints.
- [ ] **Push**: FCM integration.
