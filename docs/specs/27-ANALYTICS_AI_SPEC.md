# 🧠 Analytics & AI Module Specification

**Version**: 1.0.0
**Status**: Phase 3 (Future)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Analytics & AI module is the "Intelligence Center" of ShuleLabs. It provides advanced analytics capabilities including student performance analytics, predictive models for at-risk student identification, financial forecasting, HR analytics, custom dashboard building, data visualization widgets, AI-powered insights, and trend analysis. This module transforms data into actionable intelligence.

### 1.2 User Stories

- **As a Principal**, I want to identify students at risk of failing, so that I can intervene early.
- **As a Finance Director**, I want to forecast fee collection, so that I can plan cash flow.
- **As an Academic Director**, I want to analyze performance trends across terms, so that I can improve curriculum.
- **As an HR Manager**, I want workforce analytics, so that I can optimize staffing.
- **As an Admin**, I want to create custom dashboards, so that I can monitor what matters to me.
- **As a Teacher**, I want AI-generated insights about my class, so that I can focus my efforts.

### 1.3 Analytics Categories

#### Academic Analytics
- Student performance scoring
- Class/subject performance comparisons
- Attendance correlation analysis
- Learning outcome predictions

#### Financial Analytics
- Collection forecasting
- Outstanding balance trends
- Payment behavior analysis
- Budget vs actual reporting

#### HR Analytics
- Workforce distribution
- Attendance patterns
- Leave utilization
- Teacher workload analysis

#### Operational Analytics
- Resource utilization
- Process efficiency metrics
- Capacity planning
- Service level analysis

### 1.4 Acceptance Criteria

- [ ] Performance dashboard with drill-down.
- [ ] At-risk student prediction model.
- [ ] Fee collection forecasting.
- [ ] Custom dashboard builder.
- [ ] Data export for external analysis.
- [ ] Scheduled report generation.
- [ ] AI-powered insight summaries.
- [ ] Trend visualization over time.
- [ ] All data scoped by school_id.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `analytics_datasets`
Materialized data for analytics.
```sql
CREATE TABLE analytics_datasets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    dataset_type VARCHAR(100) NOT NULL,
    dataset_key VARCHAR(100),
    period_type ENUM('daily', 'weekly', 'monthly', 'term', 'yearly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    data JSON NOT NULL,
    aggregation_level VARCHAR(50),
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_type_period (school_id, dataset_type, period_start),
    INDEX idx_expires (expires_at)
);
```

#### `predictive_models`
ML model configurations.
```sql
CREATE TABLE predictive_models (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT,
    model_type VARCHAR(100) NOT NULL,
    model_name VARCHAR(150) NOT NULL,
    version VARCHAR(20) NOT NULL,
    algorithm VARCHAR(100) NOT NULL,
    features JSON NOT NULL,
    hyperparameters JSON,
    training_metrics JSON,
    model_path VARCHAR(500),
    status ENUM('training', 'active', 'deprecated') DEFAULT 'training',
    trained_at DATETIME,
    last_used_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_type_status (model_type, status)
);
```

#### `predictions`
Stored predictions.
```sql
CREATE TABLE predictions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    model_id INT NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT NOT NULL,
    prediction_type VARCHAR(100) NOT NULL,
    prediction_value DECIMAL(10,4),
    prediction_label VARCHAR(100),
    confidence DECIMAL(5,4),
    factors JSON,
    valid_until DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES predictive_models(id) ON DELETE CASCADE,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_type (prediction_type, created_at)
);
```

#### `custom_dashboards`
User-created dashboards.
```sql
CREATE TABLE custom_dashboards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    layout JSON NOT NULL,
    widgets JSON NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_shared BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

#### `insight_suggestions`
AI-generated insights.
```sql
CREATE TABLE insight_suggestions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    insight_type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    data JSON,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    target_audience JSON,
    action_suggestions JSON,
    is_read BOOLEAN DEFAULT FALSE,
    is_dismissed BOOLEAN DEFAULT FALSE,
    valid_from DATE,
    valid_until DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_type (school_id, insight_type),
    INDEX idx_validity (valid_from, valid_until)
);
```

### 2.2 Predictive Models

#### At-Risk Student Model
```python
Features:
- Attendance rate (last 30 days)
- Grade trend (improving/declining)
- Assignment submission rate
- Fee payment status
- Historical performance

Output:
- Risk score (0-100)
- Risk category (low, medium, high)
- Contributing factors
```

#### Fee Collection Forecast
```python
Features:
- Historical collection patterns
- Outstanding balance
- Payment method preferences
- Term timeline
- Economic factors (if available)

Output:
- Predicted collection (next 30/60/90 days)
- Confidence interval
```

### 2.3 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Analytics** |
| GET | `/api/v1/analytics/academic/overview` | Academic overview | Admin |
| GET | `/api/v1/analytics/academic/students/{id}` | Student analytics | Teacher |
| GET | `/api/v1/analytics/academic/classes/{id}` | Class analytics | Teacher |
| GET | `/api/v1/analytics/financial/overview` | Financial overview | Finance |
| GET | `/api/v1/analytics/financial/forecast` | Collection forecast | Finance |
| GET | `/api/v1/analytics/hr/overview` | HR analytics | HR |
| **Predictions** |
| GET | `/api/v1/analytics/predictions/at-risk` | At-risk students | Teacher |
| GET | `/api/v1/analytics/predictions/student/{id}` | Student predictions | Teacher |
| **Dashboards** |
| GET | `/api/v1/analytics/dashboards` | List dashboards | User |
| POST | `/api/v1/analytics/dashboards` | Create dashboard | User |
| PUT | `/api/v1/analytics/dashboards/{id}` | Update dashboard | User |
| GET | `/api/v1/analytics/dashboards/{id}/data` | Dashboard data | User |
| **Insights** |
| GET | `/api/v1/analytics/insights` | Get insights | User |
| POST | `/api/v1/analytics/insights/{id}/dismiss` | Dismiss insight | User |
| **Widgets** |
| GET | `/api/v1/analytics/widgets/catalog` | Available widgets | User |
| GET | `/api/v1/analytics/widgets/{type}/data` | Widget data | User |

### 2.4 Module Structure

```
app/Modules/Analytics/
├── Config/
│   ├── Routes.php
│   └── Analytics.php
├── Controllers/
│   ├── Api/
│   │   ├── AcademicAnalyticsController.php
│   │   ├── FinancialAnalyticsController.php
│   │   ├── HRAnalyticsController.php
│   │   ├── PredictionController.php
│   │   ├── DashboardController.php
│   │   ├── InsightController.php
│   │   └── WidgetController.php
│   └── Web/
│       └── AnalyticsDashboardController.php
├── Models/
│   ├── AnalyticsDatasetModel.php
│   ├── PredictiveModelModel.php
│   ├── PredictionModel.php
│   ├── CustomDashboardModel.php
│   └── InsightSuggestionModel.php
├── Services/
│   ├── Academic/
│   │   ├── StudentPerformanceService.php
│   │   ├── ClassAnalyticsService.php
│   │   └── AttendanceAnalyticsService.php
│   ├── Financial/
│   │   ├── CollectionAnalyticsService.php
│   │   └── ForecastingService.php
│   ├── HR/
│   │   └── WorkforceAnalyticsService.php
│   ├── Predictions/
│   │   ├── AtRiskPredictor.php
│   │   └── CollectionPredictor.php
│   ├── DashboardService.php
│   ├── WidgetService.php
│   └── InsightGeneratorService.php
├── Widgets/
│   ├── BaseWidget.php
│   ├── ChartWidget.php
│   ├── MetricWidget.php
│   ├── TableWidget.php
│   └── MapWidget.php
├── Jobs/
│   ├── RefreshAnalyticsDatasetsJob.php
│   ├── RunPredictionsJob.php
│   └── GenerateInsightsJob.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateAnalyticsTables.php
├── Views/
│   └── dashboard/
│       └── builder.php
└── Tests/
    ├── Unit/
    │   └── AtRiskPredictorTest.php
    └── Feature/
        └── AnalyticsApiTest.php
```

### 2.5 Integration Points

- **Academics Module**: Academic performance data.
- **Finance Module**: Financial transaction data.
- **HR Module**: Staff and payroll data.
- **Reports Module**: Analytics as report source.
- **Scheduler Module**: Dataset refresh jobs.

---

## Part 3: Widget Catalog

| Widget | Type | Data Source | Description |
|:-------|:-----|:------------|:------------|
| `performance_trend` | Line Chart | Academic | Grade trends over time |
| `attendance_heatmap` | Heatmap | Academic | Daily attendance patterns |
| `fee_collection_gauge` | Gauge | Finance | Collection vs target |
| `outstanding_by_class` | Bar Chart | Finance | Balances by class |
| `at_risk_list` | Table | Predictions | At-risk student list |
| `staff_distribution` | Pie Chart | HR | Staff by department |
| `enrollment_trend` | Area Chart | Academic | Student numbers over time |

---

## Part 4: Development Checklist

- [ ] **Datasets**: Materialization pipeline.
- [ ] **Academic**: Performance analytics.
- [ ] **Financial**: Collection analytics.
- [ ] **Predictions**: At-risk model.
- [ ] **Predictions**: Collection forecast.
- [ ] **Dashboards**: Builder UI.
- [ ] **Widgets**: Core widget set.
- [ ] **Insights**: AI generation.
- [ ] **Export**: Data export.
