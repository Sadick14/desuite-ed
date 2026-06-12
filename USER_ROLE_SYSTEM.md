# User Role System

## Overview

The system now has a clear distinction between **self-registered users** and **admin-added users**:

- **Self-registered users** (sign up via registration form) → Automatically assigned **Owner** role
- **Admin-added users** (created by admin in user management) → Assigned **Member** role (or any role admin chooses)

---

## Role Hierarchy

| Role | Level | Description |
|------|-------|-------------|
| **Owner** | 6 | Full system access. Created by self-registration. |
| **Admin** | 5 | Administrative access. Can manage users, courses, grades, payments. |
| **Teacher** | 4 | Can manage attendance, view/manage grades, view students/courses. |
| **Member** | 3 | Basic member access. View students and courses. Default for admin-added users. |
| **Parent** | 2 | Limited access. View their child's grades, attendance, reports. |
| **Student** | 1 | View-only access for their own grades, courses, attendance. |

---

## How It Works

### Registration Flow (Self-Registered Users)

1. User navigates to registration page
2. Fills in: Name, Email, Password
3. Clicks "Register"
4. **System:**
   - Creates user account
   - **Automatically sets role to `Owner`**
   - Creates personal team
   - Redirects to dashboard

**Result:** User has full system access

---

### Admin User Creation Flow (Admin-Added Users)

1. Admin navigates to `/users`
2. Clicks "Create User" button
3. Fills in: Name, Email, Password, **Role** (dropdown)
4. Clicks "Create"
5. **System:**
   - Creates user account
   - Sets role to selected option (or defaults to `Member`)
   - No personal team created
   - Redirects to user list

**Result:** User has access based on assigned role

---

## Code Changes

### CreateNewUser Action
**File:** `app/Actions/Fortify/CreateNewUser.php`

```php
$user = User::create([
    'name' => $input['name'],
    'email' => $input['email'],
    'password' => $input['password'],
    'role' => TeamRole::Owner, // ← Self-registered users are owners
]);
```

### UserController (Admin User Creation)
**File:** `app/Http/Controllers/UserController.php`

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:8',
        'role' => 'nullable|in:admin,teacher,student,parent,member',
    ]);

    $validated['role'] = $validated['role'] ?? TeamRole::Member; // Default to Member
    
    User::create($validated);
    
    return redirect()->route('users.index')->with('success', 'User created successfully!');
}
```

---

## Permissions by Role

### Owner
- Full access to all features
- Can manage all users
- Can manage school settings
- Can manage finances
- Can manage academic records
- Can generate all reports

### Admin
- Manage students ✓
- View students ✓
- Manage courses ✓
- View courses ✓
- Manage exams ✓
- View exams ✓
- Manage grades ✓
- View grades ✓
- Manage attendance ✓
- View attendance ✓
- Manage payments ✓
- View payments ✓
- Manage expenses ✓
- View expenses ✓
- Send SMS ✓
- View SMS logs ✓
- Generate reports ✓
- View reports ✓

### Teacher
- View students ✓
- View courses ✓
- View exams ✓
- Manage grades ✓
- View grades ✓
- Manage attendance ✓
- View attendance ✓
- View reports ✓

### Member (Default for admin-added users)
- View students ✓
- View courses ✓

### Parent
- View students (their children) ✓
- View grades ✓
- View attendance ✓
- View reports ✓

### Student
- View students ✓
- View courses ✓
- View exams ✓
- View grades ✓
- View attendance ✓
- View reports ✓

---

## User Workflows

### Scenario 1: New School Registering

1. School principal creates account via public registration
   - Gets `Owner` role automatically
   - Can access everything
   - Can create admin account

2. Principal creates teacher accounts via Admin panel
   - Sets role to `Teacher`
   - Teachers can manage grades and attendance

3. Principal creates staff accounts
   - Sets role to `Member` (or specific roles)
   - Staff can view student data

**Result:** Clear hierarchy with principal as owner

---

### Scenario 2: Existing Owner Adds Team Members

1. Owner logs in, goes to `/users`
2. Owner creates an Admin account
   - Fills: Name, Email, Password
   - Selects: Admin role
   - Saves
3. Admin gets full administrative access (except user management can still be restricted)
4. Admin creates Teachers
   - Fills: Name, Email, Password
   - Selects: Teacher role
   - Teachers can now enter grades

**Result:** Hierarchy: Owner → Admin → Teachers

---

## Checking User Roles in Code

### In Controllers
```php
if ($user->hasRole(TeamRole::Owner)) {
    // Owner-only logic
}

if ($user->hasAnyRole([TeamRole::Admin, TeamRole::Owner])) {
    // Admin and Owner can do this
}

if ($user->hasPermission(TeamPermission::ManageGrades)) {
    // User can manage grades
}
```

### In Blade/Vue
```blade
@if ($user->hasRole(\App\Enums\TeamRole::Owner))
    <!-- Owner-only content -->
@endif

@if ($user->hasPermission(\App\Enums\TeamPermission::ManageGrades))
    <!-- Content for users who can manage grades -->
@endif
```

---

## Testing the Role System

### Test 1: Self-Registered User Gets Owner Role
1. Go to `/register` (public page)
2. Register new account
3. Check user in database → role should be `owner`
4. Verify owner has full access

### Test 2: Admin-Added User Gets Member Role (Default)
1. Log in as owner
2. Go to `/users`
3. Create new user (leave role empty or select)
4. Check user in database → role should be `member` (or selected role)
5. Verify member has limited access

### Test 3: Admin-Added User With Specific Role
1. Log in as owner
2. Go to `/users`
3. Create new user, select "Teacher"
4. Check user in database → role should be `teacher`
5. Log in as teacher → verify teacher-specific access

### Test 4: Role-Based Permissions Work
1. Create users with different roles
2. Log in as each role
3. Verify they can only access what their role permits

---

## Summary

✅ **Self-registered users** automatically become **Owners**  
✅ **Admin-added users** default to **Members** (or assigned role)  
✅ **Clear permission model** - each role has specific abilities  
✅ **Hierarchy enforced** - higher roles include lower role permissions  
✅ **No ambiguity** - roles clearly define access levels  

**The system now properly distinguishes between:**
- People who created the system (Owners)
- People the owner invited to the system (Members/Admins/Teachers/etc.)
