# 🤖 AI Extensions Module Specification

**Version**: 1.0.0
**Status**: Phase 3 (Future)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The AI Extensions module is the "Future Brain" of ShuleLabs. It provides advanced AI-powered capabilities including chatbots for support, intelligent recommendations, automated content generation, sentiment analysis, natural language queries, image recognition, voice assistants, and anomaly detection. This module leverages machine learning to enhance user experience and automate complex tasks.

### 1.2 AI Capabilities

#### Conversational AI
- **Support Chatbot**: Answers common questions about fees, schedules, policies.
- **Voice Assistant**: Voice commands for teachers (attendance, grades).
- **Natural Language Query**: "Show me students with low attendance this month."

#### Intelligent Automation
- **Smart Recommendations**: Suggest interventions for at-risk students.
- **Content Generation**: Auto-generate report card comments.
- **Schedule Optimization**: Suggest optimal timetables.

#### Recognition & Analysis
- **Image Recognition**: Verify student ID cards, process documents.
- **Sentiment Analysis**: Analyze parent feedback sentiment.
- **Anomaly Detection**: Detect unusual patterns in data.

### 1.3 User Stories

- **As a Parent**, I want to ask a chatbot about my child's fees, so that I get answers quickly.
- **As a Teacher**, I want AI to suggest report card comments, so that I save time writing.
- **As an Admin**, I want to search data using natural language, so that I don't need complex filters.
- **As a Principal**, I want to detect anomalies in attendance patterns, so that I can investigate issues.
- **As a Support Staff**, I want the chatbot to handle routine queries, so that I can focus on complex issues.

### 1.4 Acceptance Criteria

- [ ] Chatbot answers common FAQs accurately.
- [ ] NL queries converted to database queries.
- [ ] Comment generator produces quality text.
- [ ] Image recognition validates ID cards.
- [ ] Sentiment analysis categorizes feedback.
- [ ] Anomaly detection alerts on unusual patterns.
- [ ] Integration with existing modules.
- [ ] User feedback loop for improvements.
- [ ] Privacy and consent management.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `ai_conversations`
Chatbot conversation history.
```sql
CREATE TABLE ai_conversations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT,
    session_id VARCHAR(100) NOT NULL,
    channel ENUM('web', 'mobile', 'whatsapp', 'voice') DEFAULT 'web',
    status ENUM('active', 'closed', 'escalated') DEFAULT 'active',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME,
    satisfaction_rating INT,
    escalated_to INT,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_id),
    INDEX idx_user (user_id, started_at)
);
```

#### `ai_messages`
Individual messages.
```sql
CREATE TABLE ai_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content TEXT NOT NULL,
    intent VARCHAR(100),
    confidence DECIMAL(5,4),
    entities JSON,
    tokens_used INT,
    latency_ms INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id)
);
```

#### `ai_intents`
Intent definitions for chatbot.
```sql
CREATE TABLE ai_intents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT,
    intent_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    training_phrases JSON NOT NULL,
    responses JSON NOT NULL,
    action_type ENUM('respond', 'query', 'action', 'escalate') DEFAULT 'respond',
    action_config JSON,
    priority INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_category (category)
);
```

#### `ai_content_generations`
Generated content records.
```sql
CREATE TABLE ai_content_generations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT NOT NULL,
    content_type VARCHAR(100) NOT NULL,
    prompt TEXT NOT NULL,
    generated_content TEXT NOT NULL,
    model_used VARCHAR(100),
    tokens_used INT,
    was_accepted BOOLEAN,
    feedback TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `ai_detections`
Anomaly and pattern detections.
```sql
CREATE TABLE ai_detections (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    detection_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'warning',
    description TEXT NOT NULL,
    data JSON,
    status ENUM('new', 'reviewing', 'confirmed', 'dismissed') DEFAULT 'new',
    reviewed_by INT,
    reviewed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_type_status (detection_type, status)
);
```

### 2.2 AI Service Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    AI Gateway Service                        │
├─────────────────────────────────────────────────────────────┤
│  Request Router → Rate Limiter → Auth → Service Dispatcher  │
└───────────────────────────┬─────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        ▼                   ▼                   ▼
┌───────────────┐  ┌───────────────┐  ┌───────────────┐
│   Chatbot     │  │   NL Query    │  │   Content     │
│   Service     │  │   Service     │  │   Generator   │
├───────────────┤  ├───────────────┤  ├───────────────┤
│ Intent Match  │  │ Query Parser  │  │ Prompt Build  │
│ Response Gen  │  │ SQL Generate  │  │ LLM Call      │
│ Context Mgmt  │  │ Validation    │  │ Post Process  │
└───────────────┘  └───────────────┘  └───────────────┘
        │                   │                   │
        └───────────────────┴───────────────────┘
                            │
                    ┌───────────────┐
                    │  LLM Provider │
                    │  (OpenAI etc) │
                    └───────────────┘
```

### 2.3 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Chatbot** |
| POST | `/api/v1/ai/chat` | Send message | User |
| GET | `/api/v1/ai/chat/history` | Conversation history | User |
| POST | `/api/v1/ai/chat/feedback` | Rate response | User |
| **NL Query** |
| POST | `/api/v1/ai/query` | Natural language query | User |
| GET | `/api/v1/ai/query/suggestions` | Query suggestions | User |
| **Generation** |
| POST | `/api/v1/ai/generate/comment` | Generate comment | Teacher |
| POST | `/api/v1/ai/generate/summary` | Generate summary | Admin |
| **Detection** |
| GET | `/api/v1/ai/detections` | List detections | Admin |
| POST | `/api/v1/ai/detections/{id}/review` | Review detection | Admin |
| **Image** |
| POST | `/api/v1/ai/image/verify-id` | Verify ID card | Staff |
| POST | `/api/v1/ai/image/extract-text` | OCR extraction | Staff |

### 2.4 Module Structure

```
app/Modules/AIExtensions/
├── Config/
│   ├── Routes.php
│   └── AI.php
├── Controllers/
│   ├── Api/
│   │   ├── ChatbotController.php
│   │   ├── NLQueryController.php
│   │   ├── GenerationController.php
│   │   ├── DetectionController.php
│   │   └── ImageController.php
│   └── Web/
│       └── AIController.php
├── Models/
│   ├── AIConversationModel.php
│   ├── AIMessageModel.php
│   ├── AIIntentModel.php
│   ├── AIContentGenerationModel.php
│   └── AIDetectionModel.php
├── Services/
│   ├── ChatbotService.php
│   ├── IntentMatcherService.php
│   ├── NLQueryService.php
│   ├── ContentGeneratorService.php
│   ├── AnomalyDetectorService.php
│   ├── ImageRecognitionService.php
│   └── SentimentAnalyzerService.php
├── Providers/
│   ├── OpenAIProvider.php
│   ├── ClaudeProvider.php
│   └── LocalModelProvider.php
├── Jobs/
│   ├── RunAnomalyDetectionJob.php
│   └── TrainIntentModelJob.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateAITables.php
└── Tests/
    ├── Unit/
    │   └── IntentMatcherTest.php
    └── Feature/
        └── ChatbotApiTest.php
```

### 2.5 Integration Points

- **All Modules**: Data source for queries.
- **Threads Module**: Chatbot channel.
- **Learning Module**: Comment generation.
- **Analytics Module**: Anomaly source data.
- **Integrations Module**: LLM API calls.

---

## Part 3: Privacy & Ethics

### 3.1 Data Privacy
- Conversations anonymized after retention period.
- PII not sent to external LLMs.
- Consent required for AI features.

### 3.2 Transparency
- Users informed when interacting with AI.
- Easy escalation to human support.
- Explanation of AI decisions.

### 3.3 Fairness
- Regular bias audits.
- Diverse training data.
- Human oversight for critical decisions.

---

## Part 4: Development Checklist

- [ ] **Chatbot**: Intent matching.
- [ ] **Chatbot**: Response generation.
- [ ] **NL Query**: Parser.
- [ ] **NL Query**: SQL generation.
- [ ] **Content**: Comment generator.
- [ ] **Detection**: Anomaly patterns.
- [ ] **Image**: ID verification.
- [ ] **Providers**: LLM integration.
- [ ] **Privacy**: Data handling.
