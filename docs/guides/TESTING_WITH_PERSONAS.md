# Testing with Personas

This guide explains how to use the Persona Traffic Simulator to test the application under load and simulate real-user behavior.

## Overview

The Persona Traffic Simulator (`scripts/simulate_traffic.php`) is a CLI tool that spawns multiple "persona" processes. Each persona represents a user (Student, Teacher, Admin) performing actions on the site.

## Usage

### Quick Start

To launch a simulation with default settings (5 students, 2 teachers, 1 admin):

```bash
./bin/enhancements/launch_personas.sh
```

### Custom Simulation

You can run the PHP script directly for more control:

```bash
php scripts/simulate_traffic.php --role student --count 10 --duration 60
```

**Arguments:**
- `--role`: The role to simulate (`student`, `teacher`, `admin`, `parent`). Default: `student`.
- `--count`: Number of concurrent users to simulate. Default: `1`.
- `--duration`: Duration of the simulation in seconds. Default: `60`.
- `--url`: Base URL of the application. Default: `http://localhost:8080`.

## How it Works

1. **Authentication**: Each persona attempts to log in using credentials defined in `tests/personas/{role}_credentials.json` (you may need to create these or the script will use default test accounts).
2. **Session Management**: The script maintains a cookie jar for each persona.
3. **Behavior Loop**:
   - The persona picks an action from its role's "Action Set" (e.g., "View Dashboard", "Submit Assignment", "Check Grades").
   - It performs the HTTP request.
   - It waits for a random "think time" (1-5 seconds).
   - It repeats until the duration expires.

## Extending

To add new behaviors (e.g., for the Hostel module), edit `scripts/simulate_traffic.php` and add new actions to the `$actions` array for the relevant role.

Example:
```php
'student' => [
    ['method' => 'GET', 'path' => '/api/hostel/hostels'],
    ['method' => 'POST', 'path' => '/api/hostel/requests', 'data' => ['request_type' => 'new_allocation']],
],
```
