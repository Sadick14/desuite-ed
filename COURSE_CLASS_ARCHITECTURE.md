# Course-Class Many-to-Many Architecture

## Overview

This document explains the new course-class relationship that allows flexible, efficient course assignment without over-engineering. The system implements a **many-to-many relationship** where:

- **One course** can be taught in **multiple classes**
- **One class** can teach **multiple courses**
- Courses are defined **once in a global pool** and then assigned to classes during class creation/editing

---

## Why This Architecture?

### Problems Solved

✅ **No Course Duplication** - Define "English Language" once; assign it to Grade 10, Grade 11, etc.  
✅ **Simple Updates** - Change a course name in one place; all classes using it see the change  
✅ **Clean Grading** - Teachers enter grades per course per class without confusion  
✅ **Scalable** - 50 classes × 10 courses = 500 links (not 500 duplicated course records)  
✅ **No Migration Hell** - No need to recreate courses when creating new classes  

---

## Database Schema

### Three Tables: `courses`, `classes`, and `class_courses` (pivot)

```
┌────────────────┐
│    courses     │ (Global Pool)
├────────────────┤
│ id (PK)        │
│ name           │ e.g., "English Language"
│ code           │ e.g., "ENG101"
│ description    │ Optional course details
│ level          │ Optional: 'jhs', 'primary', etc.
│ created_at     │
│ updated_at     │
└────────────────┘
        △
        │ (FK via pivot)
        │
┌──────────────────┐
│  class_courses   │ (Many-to-Many Link)
├──────────────────┤
│ id (PK)          │
│ class_id (FK)────┼───┐
│ course_id (FK)───┼──→ (Relationship)
│ created_at       │
│ updated_at       │
│ UNIQUE(class_id, course_id) ← Prevent duplicates
└──────────────────┘
        △
        │
┌────────────────┐
│    classes     │ (Class Registry)
├────────────────┤
│ id (PK)        │
│ name           │ e.g., "Grade 10 Alpha"
│ level          │ 'jhs', 'primary', etc.
│ created_at     │
│ updated_at     │
└────────────────┘
```

### Migration: Create Pivot Table

```php
Schema::create('class_courses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
    $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
    $table->timestamps();
    
    // Ensure a course is only assigned once per class
    $table->unique(['class_id', 'course_id']);
});
```

---

## Eloquent Models

### SchoolClass Model

```php
class SchoolClass extends Model
{
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'class_courses', 'class_id', 'course_id')
            ->withTimestamps();
    }
}

// Usage:
$class = SchoolClass::find(1);
$class->courses()->pluck('name');  // Array of course names for this class
$class->courses()->attach([1, 3, 5]); // Assign courses 1, 3, 5
$class->courses()->sync([2, 4]);      // Replace with courses 2, 4
```

### Course Model

```php
class Course extends Model
{
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_courses', 'course_id', 'class_id')
            ->withTimestamps();
    }
}

// Usage:
$course = Course::find(1);
$course->classes()->count();  // How many classes teach this course
```

---

## Admin Workflow: Create Class with Courses

### Step 1: Admin navigates to `/classes/create`

### Step 2: Form shows three fields

1. **Class Name** - text input (e.g., "Grade 10 Alpha")
2. **Class Level** - dropdown (Nursery, Primary, JHS, etc.)
3. **Assign Courses** - multi-select checkboxes

   ```
   ☐ English Language (ENG)
   ☑ Core Mathematics (MATH)
   ☑ Integrated Science (SCI)
   ☐ Social Studies (SS)
   ☑ Information Technology (IT)
   ```

### Step 3: Admin selects courses and clicks "Create Class"

#### Backend: SchoolClassController@store

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:50',
        'level' => 'required|string',
        'selected_courses' => 'required|array|min:1',
        'selected_courses.*' => 'exists:courses,id',
    ]);

    // 1. Create the class
    $class = SchoolClass::create([
        'name' => $validated['name'],
        'level' => $validated['level'],
    ]);

    // 2. Link selected courses via pivot table (class_courses)
    $class->courses()->attach($validated['selected_courses']);

    return redirect()->route('classes.index');
}
```

### Step 4: System response

- Class created in `classes` table
- 3 rows inserted into `class_courses` table (one per selected course)
- Any students added to this class automatically inherit these courses

---

## Admin Workflow: Edit Class Courses

### Access: Click Edit button on class in `/classes`

### Page: `/classes/{id}/edit`

```
Shows current class + checkbox grid of all courses
Already-assigned courses are pre-checked
```

#### Backend: SchoolClassController@update

```php
public function update(Request $request, SchoolClass $class)
{
    $validated = $request->validate([
        'selected_courses' => 'required|array|min:1',
        'selected_courses.*' => 'exists:courses,id',
    ]);

    // sync() = remove old, add new (smart update)
    $class->courses()->sync($validated['selected_courses']);

    return redirect()->route('classes.index');
}
```

**What `sync()` does:**

Before: Class has courses [1, 2, 3]  
Admin selects: [2, 4, 5]  
After: Class has courses [2, 4, 5]  
- Course 1, 3 removed automatically  
- Course 4, 5 added automatically  
- Course 2 unchanged  

---

## Teacher Workflow: Enter Grades

### Teacher navigates to Student Marks

1. **Select Term**
2. **Select Class** (e.g., "Grade 10 Alpha")
3. **Select Course** (dropdown shows only courses assigned to this class)

```sql
SELECT courses.* 
FROM courses
INNER JOIN class_courses ON class_courses.course_id = courses.id
WHERE class_courses.class_id = 5
ORDER BY courses.name;
```

### Teacher enters marks for students in that class + course

- All students in Grade 10 Alpha automatically appear
- Form shows all 6 CA fields + exam score
- System calculates grades and stores results

---

## Query Examples

### Get all courses for a specific class

```php
$class = SchoolClass::find(1);
$courses = $class->courses;  // Eloquent auto-loads via belongsToMany
// Returns: Collection of Course models
```

### Get all classes teaching a specific course

```php
$course = Course::find(1);
$classes = $course->classes;
// Returns: Collection of SchoolClass models
```

### Filter classes by number of courses

```php
$classesWithMultipleCourses = SchoolClass::with('courses')
    ->get()
    ->filter(fn($c) => $c->courses->count() > 5);
```

### Get students in a class taking a specific course

```php
$class = SchoolClass::find(1);
$students = $class->students;  // All students in the class
// Each student has access to this class's courses via: $student->class->courses
```

---

## File Structure

### Database
- `database/migrations/2026_06_11_140000_restructure_courses_to_many_to_many.php`

### Models
- `app/Models/SchoolClass.php` - Updated with `courses()` relationship
- `app/Models/Course.php` - Updated with `classes()` relationship

### Controllers
- `app/Http/Controllers/SchoolClassController.php` - Handles create/edit with course selection

### Views
- `resources/js/pages/Classes/Index.vue` - List classes with assigned courses
- `resources/js/pages/Classes/Create.vue` - Form to create class + select courses
- `resources/js/pages/Classes/Edit.vue` - Form to edit class + manage courses

### Routes
- `Route::resource('classes', SchoolClassController::class)` - Already exists in `routes/web.php`
- Automatic routes: `GET /classes/create`, `POST /classes`, `GET /classes/{id}/edit`, `PUT /classes/{id}`

---

## Benefits Summary

| Aspect | Before | After |
|--------|--------|-------|
| **Course Storage** | Per class (duplicated) | Global pool (single) |
| **Update Impact** | Change 1 course = 50 updates | Change 1 course = 1 update |
| **Relationship** | 1-to-many (class→courses) | Many-to-many (flexible) |
| **New Classes** | Recreate courses each time | Reuse existing courses |
| **Grading Logic** | Simple lookup | Still simple (unchanged) |
| **Data Integrity** | Possible duplicates | Unique constraint |

---

## Migration Path (If You Had Old Data)

If migrating from the old `courses.school_class_id` structure:

```php
// Pseudo-code migration
$courses = Course::whereNotNull('school_class_id')->get();
foreach ($courses as $course) {
    ClassCourse::firstOrCreate([
        'class_id' => $course->school_class_id,
        'course_id' => $course->id,
    ]);
}
// Remove school_class_id column from courses table
```

(This was already handled in the migration.)

---

## Testing Checklist

✅ Create a new class and assign 4 courses  
✅ Verify courses appear in the class detail view  
✅ Edit the class and change course selection  
✅ Verify pivot table rows updated correctly (sync worked)  
✅ Teacher selects class → only assigned courses show in dropdown  
✅ Delete a class → verify pivot rows deleted (cascade)  
✅ Verify no course duplication in database  

---

## Key Takeaway

This architecture is **simple, scalable, and maintainable**:

- **Admin**: "Create class, pick courses, done."
- **Database**: "Clean pivot table, no duplicates."
- **Teacher**: "Select class → see courses → enter marks."
- **System**: "No special magic, just standard many-to-many."
