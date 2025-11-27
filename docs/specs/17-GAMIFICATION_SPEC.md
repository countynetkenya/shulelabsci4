# 🎮 Gamification Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Gamification module is the "Engagement Engine" of ShuleLabs. It adds game-like elements to the educational experience, including points, badges, achievements, leaderboards, and challenges. This module motivates students through positive reinforcement and healthy competition while providing visibility to parents on their child's engagement.

### 1.2 User Stories

- **As a Teacher**, I want to award points to students for good behavior or academic achievement, so that positive actions are recognized.
- **As a Student**, I want to see my points balance and earned badges, so that I feel motivated to continue improving.
- **As a Student**, I want to compete on class and school leaderboards, so that I can challenge myself against peers.
- **As an Admin**, I want to configure point values for different actions, so that I can shape student behavior.
- **As a Parent**, I want to see my child's achievements and progress, so that I can encourage them at home.
- **As a Student**, I want to complete challenges and quests for bonus points, so that I have goals to work toward.

### 1.3 User Workflows

1. **Earning Points**:
   - Student completes an action (attendance, assignment, reading).
   - System checks earning rules for this action.
   - Points calculated and added to student balance.
   - Achievement thresholds checked.
   - Badge awarded if threshold met.
   - Parent notified of milestone (optional).

2. **Leaderboard Competition**:
   - Student views leaderboard (class, grade, school).
   - Leaderboard shows rankings with points.
   - Daily/weekly/term leaderboards available.
   - Top performers highlighted.
   - Recognition in announcements.

3. **Challenge Participation**:
   - Teacher creates challenge (Read 5 books this month).
   - Students opt-in to challenge.
   - Students complete required tasks.
   - Progress tracked automatically.
   - Winners announced, rewards distributed.

4. **Reward Redemption**:
   - Student accumulates points.
   - Student browses rewards catalog.
   - Student redeems points for reward.
   - Reward issued (digital certificate, privilege, item).
   - Points deducted from balance.

### 1.4 Acceptance Criteria

- [ ] Points awarded automatically for configured actions.
- [ ] Badges awarded when achievement thresholds met.
- [ ] Leaderboards calculate rankings correctly.
- [ ] Challenges track progress toward goals.
- [ ] Rewards catalog supports point redemption.
- [ ] Parents can view child's gamification profile.
- [ ] Anti-gaming measures prevent abuse.
- [ ] All data scoped by school_id.
- [ ] Historical data preserved for analytics.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `point_categories`
Types of points (academic, behavior, participation).
```sql
CREATE TABLE point_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(30),
    description TEXT,
    icon VARCHAR(100),
    color VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_code (school_id, code)
);
```

#### `earning_rules`
How points are earned.
```sql
CREATE TABLE earning_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    action_context JSON,
    points INT NOT NULL,
    max_per_day INT,
    max_per_week INT,
    cooldown_minutes INT DEFAULT 0,
    is_auto BOOLEAN DEFAULT TRUE,
    requires_approval BOOLEAN DEFAULT FALSE,
    applies_to ENUM('students', 'staff', 'all') DEFAULT 'students',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES point_categories(id) ON DELETE CASCADE,
    INDEX idx_action (action_type)
);
```

#### `points`
Point transactions.
```sql
CREATE TABLE points (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    earning_rule_id INT,
    points INT NOT NULL,
    action_type VARCHAR(100),
    action_reference_type VARCHAR(100),
    action_reference_id INT,
    description VARCHAR(255),
    awarded_by INT,
    awarded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES point_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (earning_rule_id) REFERENCES earning_rules(id) ON DELETE SET NULL,
    FOREIGN KEY (awarded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, awarded_at),
    INDEX idx_action (action_type, action_reference_id)
);
```

#### `point_balances`
Aggregated balances for performance.
```sql
CREATE TABLE point_balances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    school_id INT NOT NULL,
    category_id INT,
    period_type ENUM('all_time', 'year', 'term', 'month', 'week', 'day') DEFAULT 'all_time',
    period_value VARCHAR(20),
    total_earned INT DEFAULT 0,
    total_spent INT DEFAULT 0,
    current_balance INT AS (total_earned - total_spent) STORED,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES point_categories(id) ON DELETE SET NULL,
    UNIQUE KEY uk_user_cat_period (user_id, category_id, period_type, period_value),
    INDEX idx_school_balance (school_id, current_balance DESC)
);
```

#### `badges`
Achievement badges.
```sql
CREATE TABLE badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon_url VARCHAR(500),
    badge_type ENUM('achievement', 'milestone', 'special', 'event') DEFAULT 'achievement',
    category_id INT,
    criteria_type ENUM('points', 'count', 'streak', 'custom') DEFAULT 'points',
    criteria_value INT,
    criteria_config JSON,
    rarity ENUM('common', 'uncommon', 'rare', 'epic', 'legendary') DEFAULT 'common',
    points_bonus INT DEFAULT 0,
    is_auto_awarded BOOLEAN DEFAULT TRUE,
    is_visible BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES point_categories(id) ON DELETE SET NULL,
    INDEX idx_school_type (school_id, badge_type)
);
```

#### `user_badges`
Badges earned by users.
```sql
CREATE TABLE user_badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    earned_for VARCHAR(255),
    awarded_by INT,
    is_displayed BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    FOREIGN KEY (awarded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_user_badge (user_id, badge_id),
    INDEX idx_user (user_id),
    INDEX idx_badge (badge_id)
);
```

#### `achievements`
Progress toward achievements.
```sql
CREATE TABLE achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    achievement_type VARCHAR(50),
    target_value INT NOT NULL,
    badge_id INT,
    points_reward INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE SET NULL
);
```

#### `user_achievements`
User progress on achievements.
```sql
CREATE TABLE user_achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    current_value INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at DATETIME,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_achievement (user_id, achievement_id)
);
```

#### `leaderboards`
Leaderboard configurations.
```sql
CREATE TABLE leaderboards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    scope ENUM('school', 'grade', 'class', 'section') DEFAULT 'school',
    scope_id INT,
    category_id INT,
    period_type ENUM('all_time', 'term', 'month', 'week', 'day') DEFAULT 'week',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES point_categories(id) ON DELETE SET NULL,
    INDEX idx_school_scope (school_id, scope)
);
```

#### `challenges`
Time-limited challenges.
```sql
CREATE TABLE challenges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    challenge_type ENUM('individual', 'team', 'class') DEFAULT 'individual',
    target_type VARCHAR(100),
    target_value INT NOT NULL,
    points_reward INT NOT NULL,
    badge_id INT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    max_participants INT,
    is_public BOOLEAN DEFAULT TRUE,
    status ENUM('upcoming', 'active', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_school_status (school_id, status),
    INDEX idx_dates (start_date, end_date)
);
```

#### `challenge_participants`
Users in challenges.
```sql
CREATE TABLE challenge_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    challenge_id INT NOT NULL,
    user_id INT NOT NULL,
    current_progress INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at DATETIME,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_challenge_user (challenge_id, user_id)
);
```

#### `rewards`
Rewards catalog.
```sql
CREATE TABLE rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    reward_type ENUM('digital', 'physical', 'privilege', 'certificate') DEFAULT 'digital',
    points_cost INT NOT NULL,
    stock_quantity INT,
    image_url VARCHAR(500),
    is_limited BOOLEAN DEFAULT FALSE,
    available_from DATE,
    available_until DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_active (school_id, is_active)
);
```

#### `reward_redemptions`
Reward claims.
```sql
CREATE TABLE reward_redemptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reward_id INT NOT NULL,
    user_id INT NOT NULL,
    points_spent INT NOT NULL,
    status ENUM('pending', 'approved', 'fulfilled', 'cancelled') DEFAULT 'pending',
    fulfilled_by INT,
    fulfilled_at DATETIME,
    notes TEXT,
    redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (fulfilled_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Points** |
| GET | `/api/v1/gamification/points/my` | Get my points | User |
| GET | `/api/v1/gamification/points/history` | Point history | User |
| POST | `/api/v1/gamification/points/award` | Award points | Teacher/Admin |
| GET | `/api/v1/gamification/points/rules` | Get earning rules | All |
| **Badges** |
| GET | `/api/v1/gamification/badges` | List badges | All |
| GET | `/api/v1/gamification/badges/my` | My badges | User |
| POST | `/api/v1/gamification/badges/{id}/award` | Award badge | Admin |
| **Leaderboards** |
| GET | `/api/v1/gamification/leaderboards` | List leaderboards | All |
| GET | `/api/v1/gamification/leaderboards/{id}` | Get rankings | All |
| GET | `/api/v1/gamification/leaderboards/{id}/my-rank` | My position | User |
| **Challenges** |
| GET | `/api/v1/gamification/challenges` | List challenges | All |
| GET | `/api/v1/gamification/challenges/{id}` | Challenge details | All |
| POST | `/api/v1/gamification/challenges/{id}/join` | Join challenge | User |
| GET | `/api/v1/gamification/challenges/my` | My challenges | User |
| **Rewards** |
| GET | `/api/v1/gamification/rewards` | List rewards | All |
| POST | `/api/v1/gamification/rewards/{id}/redeem` | Redeem reward | User |
| GET | `/api/v1/gamification/rewards/redemptions/my` | My redemptions | User |
| **Profile** |
| GET | `/api/v1/gamification/profile/{user_id}` | User profile | All |
| GET | `/api/v1/gamification/profile/my` | My profile | User |

### 2.3 Module Structure

```
app/Modules/Gamification/
├── Config/
│   ├── Routes.php
│   ├── Services.php
│   └── EarningRules.php
├── Controllers/
│   ├── Api/
│   │   ├── PointController.php
│   │   ├── BadgeController.php
│   │   ├── LeaderboardController.php
│   │   ├── ChallengeController.php
│   │   ├── RewardController.php
│   │   └── ProfileController.php
│   └── Web/
│       └── GamificationDashboardController.php
├── Models/
│   ├── PointCategoryModel.php
│   ├── EarningRuleModel.php
│   ├── PointModel.php
│   ├── PointBalanceModel.php
│   ├── BadgeModel.php
│   ├── UserBadgeModel.php
│   ├── AchievementModel.php
│   ├── LeaderboardModel.php
│   ├── ChallengeModel.php
│   ├── RewardModel.php
│   └── RedemptionModel.php
├── Services/
│   ├── PointService.php
│   ├── PointCalculatorService.php
│   ├── BadgeService.php
│   ├── AchievementTracker.php
│   ├── LeaderboardService.php
│   ├── ChallengeService.php
│   ├── RewardService.php
│   └── AntiGamingService.php
├── Listeners/
│   ├── AttendancePointsListener.php
│   ├── AssignmentPointsListener.php
│   ├── LibraryPointsListener.php
│   └── AchievementChecker.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateGamificationTables.php
├── Views/
│   ├── dashboard/
│   ├── leaderboards/
│   ├── badges/
│   └── challenges/
└── Tests/
    ├── Unit/
    │   └── PointCalculatorTest.php
    └── Feature/
        └── GamificationApiTest.php
```

### 2.4 Integration Points

- **Learning Module**: Points for attendance, assignment completion, grades.
- **Library Module**: Points for books borrowed and returned.
- **Threads Module**: Notification of badges earned, challenge updates.
- **Reports Module**: Gamification tab in student profile.
- **Parent Engagement**: Parent visibility into achievements.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Anti-Gaming Measures
- Rate limiting on point-earning actions.
- Max points per day/week per action type.
- Cooldown periods between same actions.
- Anomaly detection for sudden point spikes.

### 3.2 Balance Consistency
- Use transactions for point awards.
- Maintain aggregate balances for performance.
- Periodic reconciliation of balances vs transactions.

### 3.3 Leaderboard Performance
- Pre-calculate rankings on point events.
- Cache leaderboard positions.
- Pagination for large leaderboards.

### 3.4 Fair Competition
- Leaderboards scoped appropriately (class-level).
- Time-based resets (weekly) for fresh starts.
- Multiple categories to allow diverse achievement.

---

## Part 4: Test Data Strategy

### 4.1 Seeding Strategy
- 5 point categories (Academic, Behavior, Reading, Sports, Service).
- 20 earning rules covering common actions.
- 15 badges with varied thresholds.
- 3 active challenges.
- 10 rewards in catalog.
- Sample points for 100 students.

### 4.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Earn points for attendance | Points awarded, balance updated |
| Hit badge threshold | Badge awarded, notification sent |
| Complete challenge | Points + badge awarded |
| Redeem reward | Points deducted, redemption created |
| Anti-gaming trigger | Points blocked, alert raised |

---

## Part 5: Development Checklist

- [ ] **Database**: Create migrations.
- [ ] **Points**: Award and balance tracking.
- [ ] **Badges**: Auto-award system.
- [ ] **Leaderboards**: Ranking calculations.
- [ ] **Challenges**: CRUD and progress tracking.
- [ ] **Rewards**: Catalog and redemption.
- [ ] **Listeners**: Event-based point awarding.
- [ ] **Anti-gaming**: Rate limits and detection.
- [ ] **Parent view**: Profile visibility.
