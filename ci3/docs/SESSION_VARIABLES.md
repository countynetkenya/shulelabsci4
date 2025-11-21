# Session Variables Structure

This document describes the session variable structure used in the CI4 authentication system, particularly for multi-school staff.

## Session Variables Reference

### User Identity Variables

| Variable | Type | Description | Example |
|----------|------|-------------|---------|
| `loginuserID` | int | User's CI4 user ID | `123` |
| `username` | string | User's username | `john.teacher` |
| `name` | string | User's full name | `John Smith` |
| `email` | string | User's email | `john@school.edu` |
| `photo` | string | Profile photo filename | `photo.jpg` |
| `usertypeID` | int | CI3-compatible usertype ID | `2` |
| `usertype` | string | Role name | `Teacher` |
| `user_table` | string | Source table (ci4_users or CI3 table name) | `ci4_users` |
| `loggedin` | bool | Login status flag | `true` |
| `varifyvaliduser` | bool | CI3 compatibility flag | `true` |

### School Access Variables

| Variable | Type | Description | Example | Notes |
|----------|------|-------------|---------|-------|
| `available_school_ids` | array | **NEW:** Array of all school IDs user can access | `[1, 2, 3]` | Used for school selector dropdown |
| `schoolID` | int or string | Currently active school ID | `2` | Changes when user switches schools |
| `schools` | string | Legacy comma-separated school IDs | `"1,2,3"` | For CI3 compatibility only |

### School Context Variables

| Variable | Type | Description | Example | Notes |
|----------|------|-------------|---------|-------|
| `defaultschoolyearID` | int | Active school year for current school | `5` | Set when school is selected |
| `lang` | string | Language setting for current school | `english` | Set when school is selected |

## Session Lifecycle

### 1. Login (`Auth::setUserSession()`)

When user signs in, all user identity variables are set, plus:

```php
$sessionData = [
    'loginuserID' => $user->userID,
    'username' => $user->username,
    // ... other user fields ...
    'schoolID' => $user->schoolID,  // Original comma-separated string
    'schools' => $user->schoolID,   // CI3 compatibility
    'available_school_ids' => [1, 2, 3],  // NEW: Array of accessible schools
];
```

**Key Points:**
- `available_school_ids` is set once at login and never changed
- `schoolID` remains as comma-separated string initially
- `schools` duplicates `schoolID` for legacy code

### 2. School Selection (`Auth::redirectAfterSignin()` or `School::processSelection()`)

When user selects a school (or is auto-assigned single school):

```php
session()->set([
    'schoolID' => 2,  // NOW an integer - the active school
    'defaultschoolyearID' => $siteInfo->school_year,
    'lang' => $siteInfo->language
]);
```

**Key Points:**
- `schoolID` is **overwritten** with integer ID of selected school
- `available_school_ids` is **NOT changed** - preserves full access list
- School-specific context is set

### 3. Returning to School Selector (`/school/select`)

User can revisit school selector at any time:

```php
// Get list of schools user can access
$availableSchoolIDs = session()->get('available_school_ids');

// Show current active school
$currentSchoolID = session()->get('schoolID');

// Display all available schools with current one highlighted
```

**Key Points:**
- Uses `available_school_ids` to populate dropdown
- Shows `schoolID` as currently selected
- User can switch to any school in `available_school_ids`

## Code Examples

### Checking School Access

```php
// Check if user has access to a specific school
$targetSchoolID = 3;
$availableSchoolIDs = session()->get('available_school_ids');

if (in_array($targetSchoolID, $availableSchoolIDs)) {
    // User has access - allow operation
    session()->set('schoolID', $targetSchoolID);
    // Update school context...
} else {
    // Access denied
    return redirect()->back()->with('error', 'Access denied to this school');
}
```

### Getting Current School

```php
// Get the currently active school
$currentSchoolID = session()->get('schoolID');  // Returns integer

// For database queries
$builder->where('schoolID', $currentSchoolID);
```

### Multi-School Dropdown

```php
// Controller
$availableSchoolIDs = session()->get('available_school_ids');
$currentSchoolID = session()->get('schoolID');

// Load schools from database
$schools = $this->siteModel->whereIn('id', $availableSchoolIDs)->findAll();

// View
foreach ($schools as $school) {
    $selected = ($school->id == $currentSchoolID) ? 'selected' : '';
    echo "<option value='{$school->id}' {$selected}>{$school->name}</option>";
}
```

### Legacy Code Compatibility

Old code using `schools` (comma-separated) will still work:

```php
// OLD CODE (still works)
$schoolIDs = explode(',', session()->get('schools'));  // "1,2,3" -> [1, 2, 3]

// NEW CODE (preferred)
$schoolIDs = session()->get('available_school_ids');   // [1, 2, 3]
```

## Migration from Old Session Structure

### Before (CI3 style)

```php
// User could access schools 1, 2, 3
$_SESSION['schoolID'] = '1,2,3';  // Comma-separated string
$_SESSION['schools'] = '1,2,3';   // Duplicate

// When user selects school 2:
$_SESSION['schoolID'] = '2';      // PROBLEM: Lost list of other schools!
```

**Problem:** After selecting a school, the list of available schools was lost.

### After (CI4 with available_school_ids)

```php
// User can access schools 1, 2, 3
session()->set('available_school_ids', [1, 2, 3]);  // Array - preserved
session()->set('schoolID', '1,2,3');                // Comma-separated (compatibility)

// When user selects school 2:
session()->set('schoolID', 2);                      // Integer - active school
// available_school_ids still [1, 2, 3]             // SOLUTION: List preserved!
```

**Solution:** `available_school_ids` maintains the original list, allowing users to switch schools.

## Common Patterns

### Pattern 1: Admin with Multiple Schools

```php
// Login: User has schools 1, 2, 3
available_school_ids: [1, 2, 3]
schoolID: "1,2,3"

// First redirect: /school/select shown
// User selects school 2

// After selection:
available_school_ids: [1, 2, 3]  // Unchanged
schoolID: 2                       // Active school
defaultschoolyearID: 5
lang: "english"

// Later: User clicks "Change School" -> /school/select
// Dropdown shows all 3 schools, with school 2 selected
// User can switch to school 1 or 3
```

### Pattern 2: Teacher with Single School

```php
// Login: User has school 1 only
available_school_ids: [1]
schoolID: "1"

// Auto-redirected to dashboard (no school selection needed)
available_school_ids: [1]
schoolID: 1
defaultschoolyearID: 5
lang: "english"
```

### Pattern 3: Super Admin

```php
// Login: Super admin (no school restrictions)
available_school_ids: []
schoolID: 0  // Special value for super admin

// Redirected to /admin instead of dashboard
// Can access all schools without restrictions
```

## Troubleshooting

### Issue: User Can't Switch Schools

**Check:**
```php
var_dump(session()->get('available_school_ids'));
```

**Should be:** Array with multiple school IDs  
**If not:** Session may be from old code - user needs to re-login

### Issue: School Access Denied

**Check:**
```php
$targetSchool = 3;
$available = session()->get('available_school_ids');
var_dump(in_array($targetSchool, $available));
```

**Should be:** `true` if user has access  
**If false:** User legitimately doesn't have access to that school

### Issue: Session Values Incorrect After Login

**Check in Auth controller logs:**
```
DEBUG - Auth::setUserSession() - Setting session data: {
    "loginuserID": 123,
    "usertypeID": 2,
    "usertype": "Teacher",
    "schools": "1,2,3",
    "available_school_ids": [1, 2, 3]
}
```

**Verify:**
- `available_school_ids` is an array, not a string
- Contains all schools from user's `schoolID` field

## Best Practices

1. **Always use `available_school_ids` for school access checks**
   - More reliable than parsing `schools`
   - Consistent array type

2. **Use `schoolID` (singular) for current school only**
   - After school selection, this is an integer
   - Before selection (multi-school users), it's a string

3. **Maintain backward compatibility with `schools`**
   - Keep setting it for old CI3 code
   - Eventually migrate old code to use `available_school_ids`

4. **Don't overwrite `available_school_ids` after login**
   - Set once during `Auth::setUserSession()`
   - Never modify in school selection or switching

5. **Log session state changes**
   - Helps debug issues with school access
   - Track when schools are switched

---

**Last Updated:** November 2025  
**Version:** 1.0
