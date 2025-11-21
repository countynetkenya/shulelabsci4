# CodeIgniter 3 Technical Debt Report

## Introduction

This report outlines potential issues, technical debt, and areas for improvement identified in the CodeIgniter 3 application. Addressing these issues before or during the migration to CodeIgniter 4 will help ensure a more stable, secure, and maintainable application.

## 1. Security Vulnerabilities

### 1.1. Outdated Hashing Algorithm
- **Location:** `mvc/core/MY_Model.php`
- **Issue:** The `hash()` method in the base model uses `hash("sha512", $string . config_item("encryption_key"))`. This is a fast hashing algorithm and is not salted. Modern best practices recommend using password-specific hashing functions like `password_hash()` and `password_verify()`, which are designed to be slow and include a salt automatically.
- **Recommendation:** Replace the custom `hash()` method with PHP's built-in `password_hash()` and `password_verify()` functions. This will require a one-time migration of existing user passwords.

### 1.2. Use of `xss_clean`
- **Location:** `mvc/controllers/Signin.php` and other controllers.
- **Issue:** The `xss_clean` rule is used in form validation. This feature was deprecated in CodeIgniter 3 and is known to be unreliable and can even introduce security vulnerabilities in some cases.
- **Recommendation:** Remove all instances of `xss_clean` from the form validation rules. Instead, rely on proper output escaping in the views to prevent XSS.

### 1.3. Global XSS Filtering
- **Location:** `mvc/config/config.php`
- **Issue:** `global_xss_filtering` is enabled. This feature is also deprecated and can cause unexpected behavior and performance issues.
- **Recommendation:** Disable `global_xss_filtering` and ensure all output is properly escaped in the views.

### 1.4. CSRF Protection Disabled
- **Location:** `mvc/config/config.php`
- **Issue:** `csrf_protection` is disabled. This is a significant security risk, as it leaves the application vulnerable to Cross-Site Request Forgery attacks.
- **Recommendation:** Enable CSRF protection globally and ensure that all forms are updated to include the CSRF token. The `csrf_exclude_uris` array can be used to whitelist any specific routes that need to be excluded.

## 2. Legacy Code and Best Practices

### 2.1. Manual User Authentication
- **Location:** `mvc/controllers/Signin.php`
- **Issue:** The `_userChecker()` method in the `Signin` controller manually queries multiple tables (`student`, `parents`, `teacher`, `user`, `systemadmin`) to find a matching user. This is inefficient and tightly couples the authentication logic to the database schema.
- **Recommendation:** Refactor this to use a more robust and centralized authentication library. This could involve creating a dedicated `Auth` library or service that handles user lookups and authentication from a single point.

### 2.2. Raw SQL Queries in Base Model
- **Location:** `mvc/core/MY_Model.php`
- **Issue:** The `get_where_sum()` method constructs raw SQL queries. This is generally discouraged as it can be prone to errors and makes the code harder to read and maintain.
- **Recommendation:** Refactor this method to use the CodeIgniter Query Builder, which provides a more fluent and secure interface for building queries.

### 2.3. Lack of Dependency Injection
- **Location:** Throughout the codebase.
- **Issue:** The application heavily relies on the CodeIgniter super-object (`$this->load->...`, `$this->session->...`, etc.) to access services and models. This makes the code tightly coupled and difficult to test.
- **Recommendation:** While a full transition to dependency injection is a major undertaking, we can start by creating a simple service container or using a library to manage dependencies. This will be a natural part of the migration to CodeIgniter 4, which has a built-in DI container.

## 3. Maintainability and Code Quality

### 3.1. Inconsistent Naming Conventions
- **Issue:** There are some inconsistencies in naming conventions. For example, some model methods are prefixed with `get_`, while others are not.
- **Recommendation:** Establish and enforce a consistent coding standard. This will be easier to do in the CodeIgniter 4 application.

### 3.2. "Magic Strings" for Table and Column Names
- **Issue:** Table and column names are often used as strings directly in the code.
- **Recommendation:** Define these as constants in the respective model classes to avoid typos and make the code easier to refactor.

## Conclusion

The CodeIgniter 3 application is functional, but it has accumulated a significant amount of technical debt. By addressing the issues outlined in this report, we can significantly improve the security, maintainability, and overall quality of the codebase as we migrate to CodeIgniter 4. The highest priority should be given to addressing the security vulnerabilities.
