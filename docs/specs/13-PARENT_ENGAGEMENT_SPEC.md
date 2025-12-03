# 👨‍👩‍👧 Parent Engagement Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Parent Engagement module is the "Community Builder" of the school. It provides tools for schools to actively involve parents in school life beyond academics and fees. It includes surveys, events, volunteering, parent-teacher conferences, and fundraising - all designed to strengthen the school-parent relationship and build community.

### 1.2 User Stories

- **As a Principal**, I want to send satisfaction surveys to parents, so that I can measure and improve our service.
- **As an Admin**, I want to manage school events with online RSVPs, so that I can plan for attendance.
- **As a Parent**, I want to sign up as a volunteer for school activities, so that I can contribute to my child's school.
- **As a Teacher**, I want to schedule parent-teacher conferences with available time slots, so that parents can book convenient times.
- **As a PTA Chair**, I want to run fundraising campaigns with progress tracking, so that we can meet our goals transparently.
- **As a Parent**, I want to set my communication preferences, so that I receive updates in my preferred way.

### 1.3 User Workflows

1. **Survey Distribution**:
   - Admin creates survey with questions (multiple choice, rating, open-ended).
   - Admin selects target audience (all parents, specific class, grade level).
   - Survey is published and notifications sent.
   - Parents complete survey via app or web.
   - Admin views results with analytics.

2. **Event Management**:
   - Admin creates event (Sports Day, Open House, Cultural Event).
   - Admin sets date, venue, capacity, and RSVP deadline.
   - Parents view event and register (RSVP).
   - System tracks registrations and sends reminders.
   - Post-event, admin can send thank-you messages.

3. **Volunteer Signup**:
   - Admin posts volunteer opportunity (Library helper, Field trip chaperone).
   - Admin specifies requirements (clearance, time commitment).
   - Parents view opportunities and express interest.
   - Admin approves/rejects volunteers.
   - Approved volunteers receive instructions.

4. **Parent-Teacher Conference**:
   - Teacher creates conference schedule with available slots.
   - Parents browse available times and book slot.
   - System prevents double-booking.
   - Reminders sent to both parties.
   - Teacher can add notes after conference.

5. **Fundraising Campaign**:
   - Admin creates campaign with target amount and deadline.
   - Campaign page shared with parents.
   - Parents make donations (integrated with Finance/Wallets).
   - Progress bar shows real-time fundraising status.
   - Thank-you notifications sent automatically.

### 1.4 Acceptance Criteria

- [ ] Surveys support multiple question types with required/optional options.
- [ ] Survey responses are anonymous or identified based on configuration.
- [ ] Events support capacity limits and waitlists.
- [ ] Volunteer opportunities can require admin approval.
- [ ] Conference scheduling prevents time conflicts.
- [ ] Fundraising integrates with payment gateways.
- [ ] Parents can set notification preferences.
- [ ] All features support multi-tenant (school_id) scoping.
- [ ] Analytics available for each engagement type.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `surveys`
Survey definitions.
```sql
CREATE TABLE surveys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    survey_type ENUM('satisfaction', 'feedback', 'poll', 'custom') DEFAULT 'custom',
    target_audience ENUM('all_parents', 'class', 'grade', 'specific') DEFAULT 'all_parents',
    target_criteria JSON,
    is_anonymous BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'active', 'closed', 'archived') DEFAULT 'draft',
    start_date DATETIME,
    end_date DATETIME,
    allow_multiple_submissions BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_school_status (school_id, status)
);
```

#### `survey_questions`
Questions within a survey.
```sql
CREATE TABLE survey_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    survey_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'checkbox', 'rating', 'text', 'textarea', 'scale', 'date') NOT NULL,
    options JSON,
    is_required BOOLEAN DEFAULT TRUE,
    display_order INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    INDEX idx_survey_order (survey_id, display_order)
);
```

#### `survey_responses`
Parent responses to surveys.
```sql
CREATE TABLE survey_responses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    survey_id INT NOT NULL,
    respondent_id INT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    is_complete BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    FOREIGN KEY (respondent_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_survey (survey_id)
);
```

#### `survey_answers`
Individual question answers.
```sql
CREATE TABLE survey_answers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    response_id BIGINT NOT NULL,
    question_id INT NOT NULL,
    answer_value TEXT,
    answer_json JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE,
    INDEX idx_response (response_id),
    INDEX idx_question (question_id)
);
```

#### `events`
School events for parent participation.
```sql
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_type ENUM('academic', 'sports', 'cultural', 'meeting', 'workshop', 'other') DEFAULT 'other',
    venue VARCHAR(255),
    venue_address TEXT,
    venue_latitude DECIMAL(10,8),
    venue_longitude DECIMAL(11,8),
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME,
    is_all_day BOOLEAN DEFAULT FALSE,
    capacity INT,
    requires_rsvp BOOLEAN DEFAULT TRUE,
    rsvp_deadline DATETIME,
    target_audience ENUM('parents', 'students', 'staff', 'all') DEFAULT 'parents',
    target_criteria JSON,
    allow_guests BOOLEAN DEFAULT FALSE,
    max_guests_per_registration INT DEFAULT 0,
    cover_image_path VARCHAR(500),
    attachments JSON,
    status ENUM('draft', 'published', 'cancelled', 'completed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_school_date (school_id, start_datetime),
    INDEX idx_status (school_id, status)
);
```

#### `event_registrations`
RSVPs and attendance tracking.
```sql
CREATE TABLE event_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    num_guests INT DEFAULT 0,
    guest_names JSON,
    status ENUM('registered', 'waitlisted', 'cancelled', 'attended', 'no_show') DEFAULT 'registered',
    dietary_requirements TEXT,
    special_needs TEXT,
    notes TEXT,
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    checked_in_at DATETIME,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_event_user (event_id, user_id),
    INDEX idx_event_status (event_id, status)
);
```

#### `volunteer_opportunities`
Volunteer positions.
```sql
CREATE TABLE volunteer_opportunities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('classroom', 'library', 'field_trip', 'event', 'sports', 'office', 'other') DEFAULT 'other',
    date_from DATE,
    date_to DATE,
    time_from TIME,
    time_to TIME,
    is_recurring BOOLEAN DEFAULT FALSE,
    recurrence_pattern VARCHAR(100),
    location VARCHAR(255),
    spots_available INT,
    requirements TEXT,
    requires_clearance BOOLEAN DEFAULT FALSE,
    requires_approval BOOLEAN DEFAULT TRUE,
    contact_person_id INT,
    status ENUM('open', 'filled', 'closed', 'cancelled') DEFAULT 'open',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_person_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_school_status (school_id, status)
);
```

#### `volunteers`
Parent volunteer sign-ups.
```sql
CREATE TABLE volunteers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    opportunity_id INT NOT NULL,
    parent_id INT NOT NULL,
    status ENUM('applied', 'approved', 'rejected', 'withdrawn', 'completed') DEFAULT 'applied',
    availability_notes TEXT,
    clearance_verified BOOLEAN DEFAULT FALSE,
    clearance_verified_at DATETIME,
    clearance_verified_by INT,
    approved_by INT,
    approved_at DATETIME,
    rejection_reason TEXT,
    hours_contributed DECIMAL(5,2) DEFAULT 0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (opportunity_id) REFERENCES volunteer_opportunities(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_opportunity_parent (opportunity_id, parent_id)
);
```

#### `conferences`
Parent-teacher conference schedules.
```sql
CREATE TABLE conferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    conference_type ENUM('parent_teacher', 'pta', 'individual', 'group') DEFAULT 'parent_teacher',
    date_from DATE NOT NULL,
    date_to DATE NOT NULL,
    slot_duration_minutes INT DEFAULT 15,
    break_between_slots INT DEFAULT 5,
    location VARCHAR(255),
    is_virtual_allowed BOOLEAN DEFAULT FALSE,
    virtual_platform VARCHAR(100),
    status ENUM('draft', 'open', 'closed', 'completed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_school_dates (school_id, date_from, date_to)
);
```

#### `conference_slots`
Available time slots for booking.
```sql
CREATE TABLE conference_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conference_id INT NOT NULL,
    teacher_id INT NOT NULL,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_virtual BOOLEAN DEFAULT FALSE,
    virtual_link VARCHAR(500),
    room VARCHAR(50),
    status ENUM('available', 'booked', 'blocked') DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conference_id) REFERENCES conferences(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conference_teacher (conference_id, teacher_id),
    INDEX idx_date_time (slot_date, start_time)
);
```

#### `conference_bookings`
Parent bookings for conference slots.
```sql
CREATE TABLE conference_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slot_id INT NOT NULL,
    parent_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'confirmed',
    parent_notes TEXT,
    teacher_notes TEXT,
    outcome TEXT,
    follow_up_required BOOLEAN DEFAULT FALSE,
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    cancelled_at DATETIME,
    FOREIGN KEY (slot_id) REFERENCES conference_slots(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_slot (slot_id),
    INDEX idx_parent (parent_id),
    INDEX idx_student (student_id)
);
```

#### `fundraising_campaigns`
PTA and school fundraising.
```sql
CREATE TABLE fundraising_campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    purpose TEXT,
    target_amount DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'KES',
    start_date DATE NOT NULL,
    end_date DATE,
    cover_image_path VARCHAR(500),
    is_public BOOLEAN DEFAULT TRUE,
    allow_anonymous BOOLEAN DEFAULT TRUE,
    minimum_donation DECIMAL(10,2) DEFAULT 0,
    status ENUM('draft', 'active', 'paused', 'completed', 'cancelled') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_school_status (school_id, status)
);
```

#### `donations`
Individual donations to campaigns.
```sql
CREATE TABLE donations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT NOT NULL,
    donor_id INT NULL,
    donor_name VARCHAR(255),
    donor_email VARCHAR(255),
    donor_phone VARCHAR(20),
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KES',
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    is_anonymous BOOLEAN DEFAULT FALSE,
    message TEXT,
    status ENUM('pending', 'confirmed', 'refunded') DEFAULT 'pending',
    donated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    confirmed_at DATETIME,
    FOREIGN KEY (campaign_id) REFERENCES fundraising_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_campaign (campaign_id),
    INDEX idx_status (status)
);
```

#### `communication_preferences`
Parent notification settings.
```sql
CREATE TABLE communication_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    school_id INT NOT NULL,
    channel_email BOOLEAN DEFAULT TRUE,
    channel_sms BOOLEAN DEFAULT TRUE,
    channel_push BOOLEAN DEFAULT TRUE,
    channel_whatsapp BOOLEAN DEFAULT FALSE,
    pref_academic BOOLEAN DEFAULT TRUE,
    pref_finance BOOLEAN DEFAULT TRUE,
    pref_events BOOLEAN DEFAULT TRUE,
    pref_surveys BOOLEAN DEFAULT TRUE,
    pref_newsletters BOOLEAN DEFAULT TRUE,
    pref_marketing BOOLEAN DEFAULT FALSE,
    quiet_hours_start TIME,
    quiet_hours_end TIME,
    language VARCHAR(10) DEFAULT 'en',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_school (user_id, school_id)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Surveys** |
| GET | `/api/v1/engagement/surveys` | List surveys | Staff/Parent |
| POST | `/api/v1/engagement/surveys` | Create survey | Admin |
| GET | `/api/v1/engagement/surveys/{id}` | Get survey details | Staff/Parent |
| PUT | `/api/v1/engagement/surveys/{id}` | Update survey | Admin |
| POST | `/api/v1/engagement/surveys/{id}/publish` | Publish survey | Admin |
| POST | `/api/v1/engagement/surveys/{id}/respond` | Submit response | Parent |
| GET | `/api/v1/engagement/surveys/{id}/results` | View results | Admin |
| **Events** |
| GET | `/api/v1/engagement/events` | List events | All |
| POST | `/api/v1/engagement/events` | Create event | Admin |
| GET | `/api/v1/engagement/events/{id}` | Get event details | All |
| POST | `/api/v1/engagement/events/{id}/register` | RSVP for event | Parent |
| DELETE | `/api/v1/engagement/events/{id}/register` | Cancel RSVP | Parent |
| POST | `/api/v1/engagement/events/{id}/checkin/{user_id}` | Check-in attendee | Staff |
| **Volunteers** |
| GET | `/api/v1/engagement/volunteers/opportunities` | List opportunities | Parent |
| POST | `/api/v1/engagement/volunteers/opportunities` | Create opportunity | Admin |
| POST | `/api/v1/engagement/volunteers/{id}/apply` | Apply to volunteer | Parent |
| POST | `/api/v1/engagement/volunteers/{id}/approve` | Approve volunteer | Admin |
| **Conferences** |
| GET | `/api/v1/engagement/conferences` | List conferences | All |
| POST | `/api/v1/engagement/conferences` | Create conference | Admin |
| GET | `/api/v1/engagement/conferences/{id}/slots` | Get available slots | Parent |
| POST | `/api/v1/engagement/conferences/slots/{id}/book` | Book slot | Parent |
| DELETE | `/api/v1/engagement/conferences/bookings/{id}` | Cancel booking | Parent |
| **Fundraising** |
| GET | `/api/v1/engagement/campaigns` | List campaigns | All |
| POST | `/api/v1/engagement/campaigns` | Create campaign | Admin |
| POST | `/api/v1/engagement/campaigns/{id}/donate` | Make donation | Parent |
| GET | `/api/v1/engagement/campaigns/{id}/donations` | List donations | Admin |
| **Preferences** |
| GET | `/api/v1/engagement/preferences` | Get preferences | User |
| PUT | `/api/v1/engagement/preferences` | Update preferences | User |

### 2.3 Module Structure

```
app/Modules/ParentEngagement/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Controllers/
│   ├── Api/
│   │   ├── SurveyController.php
│   │   ├── EventController.php
│   │   ├── VolunteerController.php
│   │   ├── ConferenceController.php
│   │   ├── FundraisingController.php
│   │   └── PreferenceController.php
│   └── Web/
│       └── EngagementDashboardController.php
├── Models/
│   ├── SurveyModel.php
│   ├── SurveyQuestionModel.php
│   ├── SurveyResponseModel.php
│   ├── EventModel.php
│   ├── EventRegistrationModel.php
│   ├── VolunteerOpportunityModel.php
│   ├── VolunteerModel.php
│   ├── ConferenceModel.php
│   ├── ConferenceSlotModel.php
│   ├── ConferenceBookingModel.php
│   ├── FundraisingCampaignModel.php
│   ├── DonationModel.php
│   └── CommunicationPreferenceModel.php
├── Services/
│   ├── SurveyService.php
│   ├── SurveyAnalyticsService.php
│   ├── EventService.php
│   ├── VolunteerService.php
│   ├── ConferenceService.php
│   ├── SlotBookingService.php
│   ├── FundraisingService.php
│   └── PreferenceService.php
├── Events/
│   ├── SurveyPublished.php
│   ├── EventCreated.php
│   ├── VolunteerApproved.php
│   ├── SlotBooked.php
│   └── DonationReceived.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateEngagementTables.php
├── Views/
│   ├── dashboard/
│   ├── surveys/
│   ├── events/
│   ├── volunteers/
│   ├── conferences/
│   └── fundraising/
└── Tests/
    ├── Unit/
    │   └── SurveyAnalyticsServiceTest.php
    └── Feature/
        └── EngagementApiTest.php
```

### 2.4 Integration Points

- **Threads Module**: Sends notifications for events, surveys, conference reminders.
- **Finance Module**: Processes donations and campaign payments.
- **Wallets Module**: Accepts donations via wallet balance.
- **Reports Module**: Engagement analytics embedded in parent view.
- **HR Module**: Retrieves teacher list for conference slots.
- **Academics Module**: Gets student-parent relationships for targeted surveys.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Survey Anonymity
- When `is_anonymous = TRUE`, do not store `respondent_id` in responses.
- IP address and user agent stored for fraud prevention but not linked to identity.
- Analytics show aggregate data only.

### 3.2 Double-Booking Prevention
- Use database transactions with row locking for conference slot booking.
- Check slot status before booking.
- Immediately update status to 'booked' on successful booking.

### 3.3 Capacity Management
- Event registrations checked against capacity before allowing RSVP.
- Waitlist auto-promoted when cancellation occurs.
- Overbooking prevented at database level.

### 3.4 Donation Security
- Payment processing via secure payment gateway.
- PCI compliance through tokenization.
- Donation confirmation only after payment verification.

### 3.5 Preference Enforcement
- All notification dispatches respect communication preferences.
- Quiet hours enforced (defer notifications until after quiet period).
- Unsubscribe from marketing without affecting critical notifications.

---

## Part 4: Embedded Reports for Entity Views
*Target Audience: Frontend Developers, Product Owners*

### 4.1 Parent View - Engagement Tab
| Field | Description |
|:------|:------------|
| Pending Surveys | Surveys awaiting response |
| Upcoming Events | Events with registration status |
| Volunteer Status | Active volunteer positions |
| Scheduled Conferences | Booked conference slots |
| Donation History | Past donations with amounts |

### 4.2 School Dashboard - Engagement Widgets
| Widget | Description |
|:-------|:------------|
| Survey Response Rate | Percentage of parents responding |
| Event Attendance | Avg attendance vs registrations |
| Volunteer Hours | Total hours contributed |
| Conference Coverage | % of parents with bookings |
| Fundraising Progress | Campaign progress bars |

---

## Part 5: Test Data Strategy
*Target Audience: QA, Developers*

### 5.1 Seeding Strategy
Use `Modules\ParentEngagement\Database\Seeds\EngagementSeeder`:

#### Surveys
- 3 surveys: 1 active, 1 closed, 1 draft.
- Mix of question types.
- 50 sample responses for closed survey.

#### Events
- 5 events: upcoming, past, cancelled.
- 30 registrations across events.

#### Volunteers
- 3 opportunities with varying status.
- 10 volunteer applications.

#### Conferences
- 1 active conference with 50 slots.
- 25 booked slots.

#### Campaigns
- 2 campaigns: 1 active, 1 completed.
- 20 donations.

### 5.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Submit anonymous survey | No respondent_id stored |
| Book already taken slot | Conflict error returned |
| Register for full event | Added to waitlist |
| Donate below minimum | Validation error |
| Respect quiet hours | Notification deferred |

---

## Part 6: Development Checklist

- [ ] **Design**: Review and approve this specification.
- [ ] **Database**: Create migrations and run.
- [ ] **Surveys**: Implement survey CRUD and response collection.
- [ ] **Surveys**: Build analytics dashboard for results.
- [ ] **Events**: Implement event management with RSVP.
- [ ] **Events**: Add waitlist with auto-promotion.
- [ ] **Volunteers**: Implement opportunity and application flow.
- [ ] **Conferences**: Implement slot generation and booking.
- [ ] **Fundraising**: Implement campaigns with payment integration.
- [ ] **Preferences**: Implement communication preference management.
- [ ] **Notifications**: Integrate with Threads module.
- [ ] **Testing**: Write unit and feature tests.
- [ ] **Review**: Code review and merge.
