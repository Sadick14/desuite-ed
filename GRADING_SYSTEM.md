# Clean Grading System Architecture

## Overview
A simplified, production-ready grading system with minimal configuration. Teachers enter marks → system calculates grades automatically → admins approve → reports generated.

---

## Database Structure

### 1. **Grading Scales** (`grading_scales` table)
- Defines grade boundaries for the institution
- Example: A = 80-100, B = 70-79, C = 60-69, etc.
- Each scale has multiple grade boundaries

### 2. **Grade Boundaries** (`grade_boundaries` table)
- Links to grading scale
- Defines min/max score ranges and corresponding letter grades + remarks
- Example: `min: 80, max: 100, grade: A, remark: Excellent`

### 3. **Assessment Settings** (`assessment_settings` table)
- **Per Term Configuration** (set once at term start)
- Defines weight distribution: CA = 40%, Exam = 60%
- Defines max marks for each component
- Links term → grading scale

### 4. **Student Marks** (`student_marks` table)
- Individual student performance record
- Fields:
  - `class_test_1`, `class_test_2`: Component scores
  - `assignment`, `participation`: Additional CA components
  - `exam_score`: Final exam score
  - `ca_total`: Normalized CA (40% of final if that's the weight)
  - `final_score`: Overall grade (0-100)
  - `letter_grade`: A/B/C/D/E/F
  - `remark`: Excellent/Good/Fair/etc
  - `status`: draft → submitted → approved

---

## Workflow

### Phase 1: Setup (Admin Only)
1. Create Grading Scale (once per institution or per year)
   - Define grade boundaries
2. Create Assessment Setting per Term
   - Choose grading scale
   - Set CA weight (default 40%)
   - Set exam weight (default 60%)
   - Set max marks for CA and exam

### Phase 2: Data Entry (Teachers)
1. Open course/term for marks entry
2. Enter raw scores for each student:
   - Class tests
   - Assignments
   - Participation
   - Final exam
3. Save as **Draft** (can edit anytime)
4. Submit for approval when ready

### Phase 3: Calculation (Automatic)
System automatically calculates:
```
CA Total = (Class Test 1 + Test 2 + Assignment + Participation) / (max possible) * 100
Normalized CA = (CA Total / 100) * CA_WEIGHT
Normalized Exam = (Exam Score / 100) * EXAM_WEIGHT
Final Score = Normalized CA + Normalized Exam
```

Then looks up Grade from GradingScale based on Final Score.

### Phase 4: Approval (Admin/HOD)
1. Review submitted marks
2. Approve (locks record) or Reject (return for revision)

### Phase 5: Reports (System)
- Generate student report cards
- Generate class/subject analytics
- Publish to student portals

---

## Controllers & Routes

| Feature | Route | Method | Who |
|---------|-------|--------|-----|
| Manage Grading Scales | `/grading-scales` | CRUD | Admin |
| Configure Assessment | `/assessment-settings` | CRUD | Admin |
| Enter Marks | `/student-marks` | Create/Update | Teacher |
| View Marks | `/student-marks` | Index | Teacher |
| Submit Marks | `POST /student-marks/{id}/submit` | POST | Teacher |
| Approve Marks | `POST /student-marks/{id}/approve` | POST | Admin |
| Reject Marks | `POST /student-marks/{id}/reject` | POST | Admin |
| Generate Reports | `/student-marks/reports/{term}` | GET | Admin |

---

## Key Features

✅ **No Complex Multi-Step Setup**: Just define scale + assessment weights once per term  
✅ **Automatic Calculations**: No manual grade calculations  
✅ **Audit Trail**: Track who submitted/approved and when  
✅ **Draft → Submission → Approval**: Prevents accidental data loss  
✅ **Simple Validation**: Scores can't exceed max marks  
✅ **Flexible Components**: Add/remove assessment components as needed  
✅ **Rank Calculation**: Ranks students within each subject per term  

---

## Next Steps

1. **Run migrations** to create tables:
   ```bash
   php artisan migrate
   ```

2. **Create frontend views** (Vue components):
   - `resources/js/pages/GradingScales/Index.vue` - View/manage scales
   - `resources/js/pages/AssessmentSettings/Index.vue` - Configure term settings
   - `resources/js/pages/StudentMarks/Index.vue` - View marks
   - `resources/js/pages/StudentMarks/Create.vue` - Enter marks spreadsheet

3. **Seed initial data** (optional):
   ```bash
   php artisan tinker
   > GradingScale::create(['name' => 'Standard', 'is_active' => true])
   > GradeBoundary::create(['grading_scale_id' => 1, 'min_score' => 80, 'max_score' => 100, 'grade' => 'A', 'remark' => 'Excellent'])
   ```

---

## Example: Student Flow

**Input (Teacher enters):**
- Class Test 1: 18/20
- Class Test 2: 17/20
- Assignment: 8/10
- Participation: 9/10
- Exam: 75/100

**System calculates:**
- CA Total = (18 + 17 + 8 + 9) / 50 * 100 = 104% → normalized to 100%
- Normalized CA = 100 * 0.40 = 40
- Normalized Exam = 75 * 0.60 = 45
- **Final Score = 85** → **Grade A** (Excellent)

---

This system is clean, scalable, and requires minimal ongoing configuration.
