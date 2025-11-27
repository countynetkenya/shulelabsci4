# API Documentation - ShuleLabs CI4

**Version:** 1.0.0  
**Last Updated:** November 23, 2025  
**Base URL:** `https://api.shulelabs.com` (Production) | `http://localhost:8080` (Development)

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Request/Response Format](#requestresponse-format)
4. [Error Handling](#error-handling)
5. [Rate Limiting](#rate-limiting)
6. [API Endpoints](#api-endpoints)
   - [Authentication](#authentication-endpoints)
   - [Finance Module](#finance-module-api)
   - [Learning Module](#learning-module-api)
   - [HR Module](#hr-module-api)
   - [Library Module](#library-module-api)
   - [Inventory Module](#inventory-module-api)
7. [Webhooks](#webhooks)
8. [Code Examples](#code-examples)

---

## Overview

The ShuleLabs CI4 API is a RESTful API that provides programmatic access to all school management system features. All responses are returned in JSON format.

### API Characteristics

- **Architecture**: REST with resource-based URLs
- **Format**: JSON request/response bodies
- **Authentication**: Session-based (Web) + JWT tokens (Mobile API)
- **Versioning**: URL-based versioning (e.g., `/api/v1/`)
- **HTTPS**: Required for production
- **CORS**: Configurable per environment

### Base URLs

| Environment | Base URL |
|------------|----------|
| Production | `https://api.shulelabs.com/api/v1` |
| Staging | `https://staging.shulelabs.com/api/v1` |
| Development | `http://localhost:8080/api/v1` |

---

## Authentication

### Session-Based Authentication (Web)

For web applications, ShuleLabs uses cookie-based session authentication.

#### Login

```http
POST /auth/signin
Content-Type: application/json

{
  "email": "admin@shulelabs.local",
  "password": "Admin@123456"
}
```

**Success Response (200 OK):**

```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "email": "admin@shulelabs.local",
      "username": "superadmin",
      "role": "SuperAdmin",
      "school_id": 1,
      "created_at": "2025-11-23T10:00:00Z"
    },
    "session_token": "eyJhbGciOiJIUzI1NiIs..."
  },
  "message": "Login successful"
}
```

**Error Response (401 Unauthorized):**

```json
{
  "status": "error",
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "Invalid email or password"
  }
}
```

#### Logout

```http
GET /auth/signout
```

**Success Response (200 OK):**

```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

### JWT Token Authentication (Mobile API)

For mobile applications, use JWT bearer tokens.

#### Get JWT Token

```http
POST /api/v1/mobile/auth/login
Content-Type: application/json

{
  "email": "teacher1@shulelabs.local",
  "password": "Teacher@123"
}
```

**Success Response (200 OK):**

```json
{
  "status": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 2,
      "email": "teacher1@shulelabs.local",
      "username": "teacher1",
      "role": "Teacher"
    }
  }
}
```

#### Using JWT Token

Include the token in the `Authorization` header:

```http
GET /api/v1/learning/students
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

#### Refresh Token

```http
POST /api/v1/mobile/auth/refresh
Content-Type: application/json

{
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Success Response (200 OK):**

```json
{
  "status": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 3600
  }
}
```

---

## Request/Response Format

### Standard Request Headers

```http
Content-Type: application/json
Authorization: Bearer {access_token}
X-Tenant-ID: {school_id}
X-Request-ID: {unique_request_id}
Accept-Language: en
```

### Standard Response Format

All API responses follow a consistent structure:

#### Success Response

```json
{
  "status": "success",
  "data": {
    // Response payload
  },
  "message": "Operation completed successfully",
  "meta": {
    "timestamp": "2025-11-23T10:00:00Z",
    "request_id": "req_abc123"
  }
}
```

#### Paginated Response

```json
{
  "status": "success",
  "data": [
    // Array of resources
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 150,
    "total_pages": 8
  }
}
```

#### Error Response

```json
{
  "status": "error",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "email": ["The email field is required"],
      "password": ["The password must be at least 8 characters"]
    }
  },
  "meta": {
    "timestamp": "2025-11-23T10:00:00Z",
    "request_id": "req_abc123"
  }
}
```

---

## Error Handling

### HTTP Status Codes

| Status Code | Description | Use Case |
|------------|-------------|----------|
| 200 | OK | Successful GET, PUT, PATCH, DELETE |
| 201 | Created | Successful POST (resource created) |
| 204 | No Content | Successful DELETE (no response body) |
| 400 | Bad Request | Invalid request format or parameters |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Authenticated but no permission |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |
| 503 | Service Unavailable | Maintenance mode |

### Error Codes

| Error Code | HTTP Status | Description |
|-----------|-------------|-------------|
| `UNAUTHORIZED` | 401 | Authentication required |
| `INVALID_CREDENTIALS` | 401 | Invalid email/password |
| `TOKEN_EXPIRED` | 401 | JWT token expired |
| `FORBIDDEN` | 403 | Insufficient permissions |
| `NOT_FOUND` | 404 | Resource not found |
| `VALIDATION_ERROR` | 422 | Input validation failed |
| `DUPLICATE_ENTRY` | 422 | Resource already exists |
| `RATE_LIMIT_EXCEEDED` | 429 | Too many requests |
| `SERVER_ERROR` | 500 | Internal server error |
| `MAINTENANCE_MODE` | 503 | System maintenance |

### Error Response Examples

#### Validation Error (422)

```json
{
  "status": "error",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "student_id": ["The student_id field is required"],
      "amount": ["The amount must be a positive number"]
    }
  }
}
```

#### Authentication Error (401)

```json
{
  "status": "error",
  "error": {
    "code": "TOKEN_EXPIRED",
    "message": "Your session has expired. Please login again."
  }
}
```

#### Permission Error (403)

```json
{
  "status": "error",
  "error": {
    "code": "FORBIDDEN",
    "message": "You do not have permission to access this resource",
    "required_role": "Admin"
  }
}
```

---

## Rate Limiting

API requests are rate-limited to ensure fair usage and system stability.

### Rate Limits

| User Type | Requests per Minute | Requests per Hour |
|----------|---------------------|-------------------|
| Anonymous | 10 | 100 |
| Authenticated | 60 | 1000 |
| Admin | 120 | 5000 |
| API Key | 300 | 10000 |

### Rate Limit Headers

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1700000000
```

### Rate Limit Exceeded Response

```json
{
  "status": "error",
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Please try again in 60 seconds.",
    "retry_after": 60
  }
}
```

---

## API Endpoints

### Authentication Endpoints

#### POST /auth/signin
Login with email and password.

**Request:**
```json
{
  "email": "admin@shulelabs.local",
  "password": "Admin@123456"
}
```

**Response (200):**
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "email": "admin@shulelabs.local",
      "role": "SuperAdmin"
    }
  }
}
```

#### GET /auth/signout
Logout current user.

**Response (200):**
```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

---

## Finance Module API

### Invoices

#### GET /api/v1/finance/invoices
List all invoices with pagination.

**Query Parameters:**
- `page` (integer, default: 1) - Page number
- `per_page` (integer, default: 20) - Items per page
- `student_id` (integer, optional) - Filter by student
- `status` (string, optional) - Filter by status: `pending`, `paid`, `overdue`
- `date_from` (string, optional) - Start date (YYYY-MM-DD)
- `date_to` (string, optional) - End date (YYYY-MM-DD)

**Request:**
```http
GET /api/v1/finance/invoices?page=1&per_page=20&status=pending
Authorization: Bearer {token}
X-Tenant-ID: 1
```

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "invoice_number": "INV-2025-001",
      "student_id": 10,
      "student_name": "John Doe",
      "amount": 50000.00,
      "paid_amount": 25000.00,
      "balance": 25000.00,
      "status": "pending",
      "due_date": "2025-12-31",
      "created_at": "2025-11-01T10:00:00Z"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 150,
    "total_pages": 8
  }
}
```

#### GET /api/v1/finance/invoices/{id}
Get a specific invoice by ID.

**Request:**
```http
GET /api/v1/finance/invoices/1
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "invoice_number": "INV-2025-001",
    "student_id": 10,
    "student": {
      "id": 10,
      "name": "John Doe",
      "grade": "Grade 10",
      "section": "A"
    },
    "items": [
      {
        "description": "Tuition Fee",
        "amount": 40000.00
      },
      {
        "description": "Lab Fee",
        "amount": 10000.00
      }
    ],
    "amount": 50000.00,
    "paid_amount": 25000.00,
    "balance": 25000.00,
    "status": "pending",
    "due_date": "2025-12-31",
    "created_at": "2025-11-01T10:00:00Z",
    "updated_at": "2025-11-23T10:00:00Z"
  }
}
```

#### POST /api/v1/finance/invoices
Create a new invoice.

**Request:**
```json
{
  "student_id": 10,
  "due_date": "2025-12-31",
  "items": [
    {
      "description": "Tuition Fee",
      "amount": 40000.00
    },
    {
      "description": "Lab Fee",
      "amount": 10000.00
    }
  ],
  "notes": "Semester 1 fees"
}
```

**Response (201):**
```json
{
  "status": "success",
  "data": {
    "id": 152,
    "invoice_number": "INV-2025-152"
  },
  "message": "Invoice created successfully"
}
```

#### PUT /api/v1/finance/invoices/{id}
Update an existing invoice.

**Request:**
```json
{
  "due_date": "2026-01-15",
  "notes": "Extended due date"
}
```

**Response (200):**
```json
{
  "status": "success",
  "data": {
    "id": 1
  },
  "message": "Invoice updated successfully"
}
```

#### DELETE /api/v1/finance/invoices/{id}
Delete an invoice.

**Request:**
```http
DELETE /api/v1/finance/invoices/1
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "status": "success",
  "data": {
    "id": 1
  },
  "message": "Invoice deleted successfully"
}
```

### Payments

#### GET /api/v1/finance/payments
List all payments.

**Query Parameters:**
- `page` (integer)
- `per_page` (integer)
- `invoice_id` (integer, optional)
- `payment_method` (string, optional): `cash`, `mpesa`, `bank_transfer`
- `date_from` (string, optional)
- `date_to` (string, optional)

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "invoice_id": 1,
      "amount": 25000.00,
      "payment_method": "mpesa",
      "transaction_id": "RK12345678",
      "status": "completed",
      "payment_date": "2025-11-15T14:30:00Z"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 200
  }
}
```

#### POST /api/v1/finance/payments
Record a payment.

**Request:**
```json
{
  "invoice_id": 1,
  "amount": 25000.00,
  "payment_method": "mpesa",
  "transaction_id": "RK12345678",
  "payment_date": "2025-11-15"
}
```

**Response (201):**
```json
{
  "status": "success",
  "data": {
    "id": 201,
    "receipt_number": "RCP-2025-201"
  },
  "message": "Payment recorded successfully"
}
```

### Fees Structure

#### GET /api/v1/finance/fees
List fee structures.

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Tuition Fee - Grade 10",
      "amount": 40000.00,
      "frequency": "semester",
      "grade_level": "Grade 10"
    }
  ]
}
```

### Transactions

#### GET /api/v1/finance/transactions
List all financial transactions.

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "type": "payment",
      "reference": "RCP-2025-201",
      "debit": 0,
      "credit": 25000.00,
      "balance": 25000.00,
      "description": "Payment for INV-2025-001",
      "created_at": "2025-11-15T14:30:00Z"
    }
  ],
  "meta": {
    "page": 1,
    "total": 500
  }
}
```

---

## Learning Module API

### Students

#### GET /api/v1/learning/students
List all students.

**Query Parameters:**
- `page` (integer)
- `per_page` (integer)
- `grade` (string, optional)
- `section` (string, optional)
- `search` (string, optional) - Search by name or admission number

**Request:**
```http
GET /api/v1/learning/students?page=1&grade=10&section=A
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 10,
      "admission_number": "STD2025010",
      "name": "John Doe",
      "email": "student1@shulelabs.local",
      "grade": "Grade 10",
      "section": "A",
      "date_of_birth": "2010-05-15",
      "gender": "M",
      "status": "active",
      "enrolled_at": "2025-01-10"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 45
  }
}
```

#### GET /api/v1/learning/students/{id}
Get student details.

**Response (200):**
```json
{
  "status": "success",
  "data": {
    "id": 10,
    "admission_number": "STD2025010",
    "name": "John Doe",
    "email": "student1@shulelabs.local",
    "grade": "Grade 10",
    "section": "A",
    "date_of_birth": "2010-05-15",
    "gender": "M",
    "guardian": {
      "name": "Jane Doe",
      "email": "parent1@shulelabs.local",
      "phone": "+254712345678"
    },
    "subjects": [
      {"id": 1, "name": "Mathematics"},
      {"id": 2, "name": "Physics"}
    ]
  }
}
```

#### POST /api/v1/learning/students
Create a new student.

**Request:**
```json
{
  "name": "Alice Smith",
  "email": "alice@example.com",
  "grade": "Grade 9",
  "section": "B",
  "date_of_birth": "2011-03-20",
  "gender": "F",
  "guardian_name": "Bob Smith",
  "guardian_email": "bob@example.com",
  "guardian_phone": "+254712345678"
}
```

**Response (201):**
```json
{
  "status": "success",
  "data": {
    "id": 46,
    "admission_number": "STD2025046"
  },
  "message": "Student created successfully"
}
```

### Classes

#### GET /api/v1/learning/classes
List all classes.

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Grade 10A",
      "grade": "Grade 10",
      "section": "A",
      "teacher_id": 2,
      "teacher_name": "Ms. Johnson",
      "student_count": 30,
      "room_number": "101"
    }
  ]
}
```

### Subjects

#### GET /api/v1/learning/subjects
List all subjects.

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Mathematics",
      "code": "MATH101",
      "credits": 4,
      "department": "Science"
    }
  ]
}
```

### Attendance

#### GET /api/v1/learning/attendance
Get attendance records.

**Query Parameters:**
- `student_id` (integer, optional)
- `class_id` (integer, optional)
- `date_from` (string, optional)
- `date_to` (string, optional)

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "student_id": 10,
      "student_name": "John Doe",
      "date": "2025-11-23",
      "status": "present",
      "remarks": ""
    }
  ]
}
```

#### POST /api/v1/learning/attendance
Mark attendance.

**Request:**
```json
{
  "class_id": 1,
  "date": "2025-11-23",
  "records": [
    {"student_id": 10, "status": "present"},
    {"student_id": 11, "status": "absent", "remarks": "Sick"}
  ]
}
```

**Response (201):**
```json
{
  "status": "success",
  "message": "Attendance marked for 2 students"
}
```

### Grades

#### GET /api/v1/learning/grades
Get student grades.

**Query Parameters:**
- `student_id` (integer, required)
- `subject_id` (integer, optional)
- `term` (string, optional)

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "student_id": 10,
      "subject_id": 1,
      "subject_name": "Mathematics",
      "term": "Term 1",
      "score": 85.5,
      "grade": "A",
      "remarks": "Excellent"
    }
  ]
}
```

#### POST /api/v1/learning/grades
Submit grades.

**Request:**
```json
{
  "student_id": 10,
  "subject_id": 1,
  "term": "Term 1",
  "score": 85.5,
  "remarks": "Excellent"
}
```

**Response (201):**
```json
{
  "status": "success",
  "data": {
    "id": 1
  },
  "message": "Grade submitted successfully"
}
```

---

## HR Module API

### Employees

#### GET /api/v1/hr/employees
List all employees.

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "employee_number": "EMP001",
      "name": "Jane Smith",
      "email": "teacher1@shulelabs.local",
      "role": "Teacher",
      "department": "Science",
      "hire_date": "2024-01-15",
      "status": "active"
    }
  ]
}
```

### Departments

#### GET /api/v1/hr/departments
List departments.

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Science Department",
      "head": "Dr. Johnson",
      "employee_count": 15
    }
  ]
}
```

### Payroll

#### GET /api/v1/hr/payroll
List payroll records.

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "employee_name": "Jane Smith",
      "month": "November 2025",
      "gross_salary": 80000.00,
      "deductions": 15000.00,
      "net_salary": 65000.00,
      "status": "processed"
    }
  ]
}
```

---

## Library Module API

### Books

#### GET /api/v1/library/books
List all books.

**Query Parameters:**
- `search` (string, optional)
- `category` (string, optional)
- `available` (boolean, optional)

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "isbn": "978-3-16-148410-0",
      "title": "Advanced Physics",
      "author": "Dr. Smith",
      "category": "Science",
      "total_copies": 10,
      "available_copies": 7,
      "shelf_location": "A-12"
    }
  ]
}
```

### Borrowing

#### POST /api/v1/library/borrowing
Borrow a book.

**Request:**
```json
{
  "book_id": 1,
  "student_id": 10,
  "due_date": "2025-12-15"
}
```

**Response (201):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "transaction_number": "BOR-2025-001"
  },
  "message": "Book borrowed successfully"
}
```

### Returns

#### POST /api/v1/library/returns
Return a book.

**Request:**
```json
{
  "borrowing_id": 1,
  "return_date": "2025-12-10",
  "condition": "good"
}
```

**Response (201):**
```json
{
  "status": "success",
  "message": "Book returned successfully"
}
```

---

## Inventory Module API

### Items

#### GET /api/v1/inventory/items
List inventory items.

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Projector",
      "category": "Electronics",
      "quantity": 15,
      "unit_price": 45000.00,
      "location": "Store Room A"
    }
  ]
}
```

### Stock

#### GET /api/v1/inventory/stock
Get stock levels.

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "item_id": 1,
      "item_name": "Projector",
      "quantity": 15,
      "reorder_level": 5,
      "status": "sufficient"
    }
  ]
}
```

### Requisitions

#### POST /api/v1/inventory/requisitions
Create requisition.

**Request:**
```json
{
  "items": [
    {
      "item_id": 1,
      "quantity": 2,
      "purpose": "Lab setup"
    }
  ],
  "department": "Science",
  "requested_by": "Dr. Johnson"
}
```

**Response (201):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "requisition_number": "REQ-2025-001"
  },
  "message": "Requisition created successfully"
}
```

---

## Webhooks

ShuleLabs can send webhook notifications for important events.

### Webhook Events

| Event | Description | Payload |
|-------|-------------|---------|
| `payment.completed` | Payment received | Payment details |
| `invoice.created` | New invoice generated | Invoice details |
| `student.enrolled` | Student enrollment | Student details |
| `grade.submitted` | Grade posted | Grade details |

### Webhook Payload Example

```json
{
  "event": "payment.completed",
  "timestamp": "2025-11-23T10:00:00Z",
  "data": {
    "id": 201,
    "invoice_id": 1,
    "amount": 25000.00,
    "payment_method": "mpesa"
  }
}
```

---

## Code Examples

### cURL Examples

#### Get Invoices
```bash
curl -X GET "http://localhost:8080/api/v1/finance/invoices?page=1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Tenant-ID: 1"
```

#### Create Invoice
```bash
curl -X POST "http://localhost:8080/api/v1/finance/invoices" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 10,
    "due_date": "2025-12-31",
    "items": [
      {"description": "Tuition Fee", "amount": 40000}
    ]
  }'
```

### JavaScript/Fetch Example

```javascript
// Login
const login = async () => {
  const response = await fetch('http://localhost:8080/auth/signin', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      email: 'admin@shulelabs.local',
      password: 'Admin@123456'
    })
  });
  
  const data = await response.json();
  return data.data.session_token;
};

// Get Students
const getStudents = async (token) => {
  const response = await fetch('http://localhost:8080/api/v1/learning/students', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'X-Tenant-ID': '1'
    }
  });
  
  return await response.json();
};
```

### PHP Example

```php
<?php

// Login
$ch = curl_init('http://localhost:8080/auth/signin');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'admin@shulelabs.local',
    'password' => 'Admin@123456'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$data = json_decode($response, true);
$token = $data['data']['session_token'];

// Get Invoices
$ch = curl_init('http://localhost:8080/api/v1/finance/invoices');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    'X-Tenant-ID: 1'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$invoices = json_decode(curl_exec($ch), true);
```

### Python Example

```python
import requests

# Login
response = requests.post('http://localhost:8080/auth/signin', json={
    'email': 'admin@shulelabs.local',
    'password': 'Admin@123456'
})
token = response.json()['data']['session_token']

# Get Students
headers = {
    'Authorization': f'Bearer {token}',
    'X-Tenant-ID': '1'
}
students = requests.get(
    'http://localhost:8080/api/v1/learning/students',
    headers=headers
).json()
```

---

## Support

For API support and questions:
- **Documentation**: https://docs.shulelabs.com
- **Email**: api-support@shulelabs.com
- **GitHub**: https://github.com/countynetkenya/shulelabsci4

---

**Last Updated**: November 23, 2025  
**Version**: 1.0.0
