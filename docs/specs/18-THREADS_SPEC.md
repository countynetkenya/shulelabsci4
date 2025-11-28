# 💬 Threads & Communications Module Specification

**Version**: 1.0.0
**Status**: Implemented (Documentation)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Threads module is the "Nervous System" of ShuleLabs. It provides unified messaging and notification capabilities across the entire platform. It handles internal messaging between users, context-linked conversations (attached to invoices, transfers, etc.), school-wide announcements, and coordinates with the Integrations module to deliver messages via SMS, Email, WhatsApp, and Push notifications.

### 1.2 User Stories

- **As a Teacher**, I want to message a parent directly about their child's behavior, so that we can communicate privately.
- **As a Parent**, I want to receive notifications when my child's invoice is generated, so that I can pay on time.
- **As an Admin**, I want to send school-wide announcements, so that all stakeholders are informed.
- **As a User**, I want to receive a thread message when an inventory item is issued to me, so that I can confirm receipt.
- **As a Principal**, I want to send targeted announcements to specific classes or grades, so that information is relevant.
- **As a Staff Member**, I want to participate in group chats with my department, so that we can collaborate effectively.

### 1.3 User Workflows

1. **Direct Messaging**:
   - User starts new conversation.
   - User selects recipient(s).
   - User composes message with optional attachments.
   - Message sent and recipient notified.
   - Recipient replies, continuing thread.
   - Read receipts tracked.

2. **Context-Linked Thread**:
   - System action occurs (invoice created, transfer initiated).
   - Thread automatically created with context link.
   - Relevant participants added.
   - Participants can discuss, confirm, or reject.
   - Thread history preserved with context.

3. **School Announcement**:
   - Admin creates announcement.
   - Admin selects audience (all, grade, class, role).
   - Announcement published with optional schedule.
   - Recipients notified via preferred channels.
   - Read tracking available.

4. **Multi-Channel Delivery**:
   - Message created in system.
   - System checks recipient preferences.
   - Message dispatched to preferred channels (Push, SMS, Email).
   - Delivery status tracked per channel.
   - Fallback to secondary channel if primary fails.

### 1.4 Acceptance Criteria

- [ ] Users can send direct messages to individuals or groups.
- [ ] Threads can be linked to system contexts (invoice, transfer, etc.).
- [ ] Announcements reach targeted audiences.
- [ ] Files can be attached to messages.
- [ ] Read receipts and delivery status available.
- [ ] Integration with SMS, Email, WhatsApp, Push.
- [ ] Notification preferences respected.
- [ ] All data scoped by school_id.
- [ ] Message search and archival supported.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `threads`
Conversation containers.
```sql
CREATE TABLE threads (
    id VARCHAR(36) PRIMARY KEY,
    school_id INT NOT NULL,
    thread_type ENUM('direct', 'group', 'context', 'announcement') DEFAULT 'direct',
    subject VARCHAR(255),
    context_type VARCHAR(100),
    context_id INT,
    status ENUM('active', 'resolved', 'archived', 'closed') DEFAULT 'active',
    is_pinned BOOLEAN DEFAULT FALSE,
    last_message_at DATETIME,
    message_count INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_context (context_type, context_id),
    INDEX idx_school_type (school_id, thread_type),
    INDEX idx_last_message (last_message_at DESC)
);
```

#### `thread_participants`
Users in a thread.
```sql
CREATE TABLE thread_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    thread_id VARCHAR(36) NOT NULL,
    user_id INT NOT NULL,
    role ENUM('owner', 'admin', 'member', 'readonly') DEFAULT 'member',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    left_at DATETIME,
    is_muted BOOLEAN DEFAULT FALSE,
    last_read_at DATETIME,
    unread_count INT DEFAULT 0,
    FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_thread_user (thread_id, user_id),
    INDEX idx_user (user_id)
);
```

#### `thread_messages`
Individual messages.
```sql
CREATE TABLE thread_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    thread_id VARCHAR(36) NOT NULL,
    sender_id INT NOT NULL,
    message_type ENUM('text', 'image', 'file', 'system', 'action') DEFAULT 'text',
    content TEXT,
    metadata JSON,
    is_important BOOLEAN DEFAULT FALSE,
    reply_to_id BIGINT,
    is_edited BOOLEAN DEFAULT FALSE,
    edited_at DATETIME,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (reply_to_id) REFERENCES thread_messages(id) ON DELETE SET NULL,
    INDEX idx_thread_created (thread_id, created_at DESC)
);
```

#### `message_attachments`
Files attached to messages.
```sql
CREATE TABLE message_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    thumbnail_path VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES thread_messages(id) ON DELETE CASCADE
);
```

#### `message_read_receipts`
Read status tracking.
```sql
CREATE TABLE message_read_receipts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT NOT NULL,
    user_id INT NOT NULL,
    read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES thread_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_message_user (message_id, user_id)
);
```

#### `announcements`
School-wide or targeted announcements.
```sql
CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    announcement_type ENUM('general', 'urgent', 'event', 'reminder') DEFAULT 'general',
    audience_type ENUM('all', 'parents', 'students', 'staff', 'class', 'grade', 'custom') DEFAULT 'all',
    audience_criteria JSON,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    channels JSON,
    attachments JSON,
    is_published BOOLEAN DEFAULT FALSE,
    publish_at DATETIME,
    expires_at DATETIME,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_school_published (school_id, is_published),
    INDEX idx_publish_date (publish_at)
);
```

#### `announcement_reads`
Announcement read tracking.
```sql
CREATE TABLE announcement_reads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    announcement_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_via ENUM('web', 'mobile', 'email', 'sms') DEFAULT 'web',
    FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_announcement_user (announcement_id, user_id)
);
```

#### `notification_queue`
Outbound notification queue.
```sql
CREATE TABLE notification_queue (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    recipient_id INT NOT NULL,
    channel ENUM('push', 'sms', 'email', 'whatsapp', 'in_app') NOT NULL,
    notification_type VARCHAR(100) NOT NULL,
    title VARCHAR(255),
    body TEXT NOT NULL,
    data JSON,
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
    status ENUM('pending', 'sent', 'delivered', 'failed', 'cancelled') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    last_attempt_at DATETIME,
    sent_at DATETIME,
    delivered_at DATETIME,
    error_message TEXT,
    external_id VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status, created_at),
    INDEX idx_recipient (recipient_id, created_at DESC)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Threads** |
| GET | `/api/v1/threads` | List user's threads | User |
| POST | `/api/v1/threads` | Create new thread | User |
| GET | `/api/v1/threads/{id}` | Get thread with messages | Participant |
| PUT | `/api/v1/threads/{id}` | Update thread | Owner/Admin |
| DELETE | `/api/v1/threads/{id}` | Archive thread | Owner |
| POST | `/api/v1/threads/{id}/participants` | Add participants | Owner/Admin |
| DELETE | `/api/v1/threads/{id}/participants/{userId}` | Remove participant | Owner/Admin |
| **Messages** |
| GET | `/api/v1/threads/{id}/messages` | Get messages (paginated) | Participant |
| POST | `/api/v1/threads/{id}/messages` | Send message | Participant |
| PUT | `/api/v1/messages/{id}` | Edit message | Sender |
| DELETE | `/api/v1/messages/{id}` | Delete message | Sender |
| POST | `/api/v1/threads/{id}/read` | Mark as read | Participant |
| **Attachments** |
| POST | `/api/v1/threads/{id}/upload` | Upload attachment | Participant |
| **Announcements** |
| GET | `/api/v1/announcements` | List announcements | User |
| POST | `/api/v1/announcements` | Create announcement | Admin |
| GET | `/api/v1/announcements/{id}` | Get announcement | User |
| PUT | `/api/v1/announcements/{id}` | Update announcement | Admin |
| POST | `/api/v1/announcements/{id}/publish` | Publish announcement | Admin |
| POST | `/api/v1/announcements/{id}/read` | Mark as read | User |
| **Context Threads** |
| GET | `/api/v1/threads/context/{type}/{id}` | Get thread for context | Authorized |
| POST | `/api/v1/threads/context/{type}/{id}` | Create context thread | System |

### 2.3 Module Structure

```
app/Modules/Threads/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Controllers/
│   ├── Api/
│   │   ├── ThreadController.php
│   │   ├── MessageController.php
│   │   ├── AnnouncementController.php
│   │   └── NotificationController.php
│   └── Web/
│       └── ThreadsWebController.php
├── Models/
│   ├── ThreadModel.php
│   ├── ThreadParticipantModel.php
│   ├── ThreadMessageModel.php
│   ├── MessageAttachmentModel.php
│   ├── MessageReadReceiptModel.php
│   ├── AnnouncementModel.php
│   ├── AnnouncementReadModel.php
│   └── NotificationQueueModel.php
├── Services/
│   ├── ThreadService.php
│   ├── MessageService.php
│   ├── AnnouncementService.php
│   ├── NotificationService.php
│   ├── ChannelDispatcher.php
│   ├── AudienceResolver.php
│   └── ReadReceiptService.php
├── Channels/
│   ├── PushChannel.php
│   ├── SmsChannel.php
│   ├── EmailChannel.php
│   ├── WhatsAppChannel.php
│   └── InAppChannel.php
├── Events/
│   ├── MessageSent.php
│   ├── ThreadCreated.php
│   └── AnnouncementPublished.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateThreadsTables.php
├── Views/
│   ├── inbox/
│   ├── thread/
│   └── announcements/
└── Tests/
    └── Feature/
        └── ThreadsApiTest.php
```

### 2.4 Integration Points

- **All Modules**: Context-linked threads for any entity.
- **Integrations Module**: SMS, Email, WhatsApp delivery.
- **Finance Module**: Invoice notifications.
- **Inventory Module**: Transfer confirmations.
- **Transport Module**: Pickup/dropoff alerts.
- **Learning Module**: Attendance notifications.
- **Scheduler Module**: Notification job processing.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Message Privacy
- Users only see threads they participate in.
- Context threads require authorization for linked entity.
- Deleted messages soft-deleted, content cleared.

### 3.2 Notification Delivery
- Retry with exponential backoff.
- Channel fallback on failure.
- Rate limiting per recipient.
- Priority queue for urgent messages.

### 3.3 Real-time Updates
- WebSocket support for live messaging.
- Polling fallback for unsupported clients.
- Push notifications for background updates.

### 3.4 Scalability
- Message pagination (50 per page).
- Unread count cached per participant.
- Archive old threads for performance.

---

## Part 4: Test Data Strategy

### 4.1 Seeding Strategy
- 20 threads (direct, group, context).
- 200 messages across threads.
- 5 announcements (published, scheduled).
- Sample notification queue entries.

### 4.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Send message to thread | Message delivered, participants notified |
| Access other's thread | Access denied |
| Publish announcement | All audience receives notification |
| Upload large file | Size limit enforced |

---

## Part 5: Development Checklist

- [x] **Database**: Tables created.
- [x] **Threads**: CRUD implemented.
- [x] **Messages**: Send/receive working.
- [ ] **Read Receipts**: Full implementation.
- [ ] **Announcements**: Targeting and scheduling.
- [ ] **Channels**: SMS, Email, WhatsApp integration.
- [ ] **Real-time**: WebSocket implementation.
- [ ] **Search**: Message search.
