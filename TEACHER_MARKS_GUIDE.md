# Teacher's Guide: How to Enter Student Marks

## Overview
Teachers enter all student marks (CA components + Exam) in a single spreadsheet view per class and course.

---

## Step-by-Step Guide

### **Step 1: Access Student Marks**

1. Log in to the system
2. Click **Academics** in the sidebar
3. Click **Student Marks**

You'll see the Student Marks dashboard.

---

### **Step 2: Quick Entry - Select Class & Course**

You'll see a section called "Quick Entry - Select Class & Course" with 4 dropdowns:

```
┌─────────────────────────────────────────────────────────────┐
│ Quick Entry - Select Class & Course                         │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ Select Term        Select Class       Select Course   Enter │
│ ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│ │ Term 1 ▼     │  │ Class 10A ▼  │  │ Math ▼       │ [Btn] │
│ └──────────────┘  └──────────────┘  └──────────────┘       │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

**Fill in the dropdowns:**

- **Select Term**: Choose the academic term (e.g., "Term 1 2026")
- **Select Class**: Choose your class (e.g., "Class 10A")
- **Select Course**: Choose the subject/course (e.g., "Mathematics")

---

### **Step 3: Click "Enter" Button**

Once all three dropdowns are filled, the "Enter" button becomes active.

Click it to open the marks entry spreadsheet.

---

### **Step 4: The Marks Entry Spreadsheet**

You'll see a table with all students in that class:

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ CLASS 10A - MATHEMATICS                                                       │
│ Term 1 2026 | 30 students                                                     │
├──────────────────────────────────────────────────────────────────────────────┤
│                                                                                │
│ Student Name        │Test 1│Test 2│Assignment│Participation│Exam *           │
├─────────────────────┼──────┼──────┼──────────┼──────────────┼────────────┤
│ John Doe            │ [ ]  │ [ ]  │ [ ]      │ [ ]          │ [ ]        │
│ Jane Smith          │ [ ]  │ [ ]  │ [ ]      │ [ ]          │ [ ]        │
│ Ahmed Hassan        │ [ ]  │ [ ]  │ [ ]      │ [ ]          │ [ ]        │
│ ... (30 students)   │      │      │          │              │            │
│                                                                                │
└──────────────────────────────────────────────────────────────────────────────┘

Progress: 0/30 students | 0%
```

---

### **Step 5: Enter Marks for Each Student**

The spreadsheet has **5 columns** for marks:

| Column | What to Enter | Example Max | Notes |
|--------|--------------|-------------|-------|
| **Test 1** | First class test score | /20 | At least ONE CA required |
| **Test 2** | Second class test score | /20 | At least ONE CA required |
| **Assignment** | Assignment/homework score | /10 | At least ONE CA required |
| **Participation** | Class participation score | /10 | At least ONE CA required |
| **Exam *** | Final exam score | /100 | **REQUIRED** - must be filled |

**How to enter:**

1. Click on a cell (input box)
2. Type the score (e.g., "18.5")
3. Press **Tab** or **Enter** to move to next cell
4. Decimals are allowed (e.g., 18.5, 19.75)

**Example Entry:**

```
Student: John Doe

Test 1: 18    (out of 20)
Test 2: 17    (out of 20)
Assignment: 8 (out of 10)
Participation: 9 (out of 10)
Exam: 75      (out of 100)

↓ System Auto-Calculates:
- CA Total: 42.5% (normalized)
- Final Score: 85.5
- Grade: A
- Remark: Excellent
```

---

### **Step 6: Progress Tracking**

As you enter marks, the progress bar updates:

```
Progress: 5/30 students | 17%

████░░░░░░░░░░░░░░░░░░░░░░░░░░
```

The exam score is required, so the system counts "completed" students as those with exam scores entered.

---

### **Step 7: Save Marks (Draft)**

When you're done entering all marks:

1. Scroll to the bottom
2. Click **"Save Marks"** button

```
┌──────────────────────────────────┐
│ [Cancel]  [✓ Save Marks]         │
└──────────────────────────────────┘
```

The marks are saved as **DRAFT** - you can edit them later if needed.

---

### **Step 8: Continue with Other Courses**

Go back to Student Marks page and repeat for the next course:

```
Class 10A - Mathematics      ✓ Saved
Class 10A - English          (next)
Class 10A - Science          (next)
Class 10A - History          (next)
... (continue for all courses)
```

---

## Key Points

### ✅ **What You Can Do:**
- Enter marks for all 5 CA components
- Leave CA components blank (only Exam is required)
- Edit marks anytime (they're in Draft status)
- Re-enter the same class/course to update marks
- Enter marks for multiple courses per class

### ⚠️ **Important Notes:**

1. **BOTH CA and Exam Scores Required for Each Student**
   - At least ONE CA score (Test 1, Test 2, Assignment, OR Participation)
   - At least ONE Exam score
   - System won't let you save without both

2. **Max Marks Reference**
   - The system shows max marks based on Assessment Settings
   - Example: CA Max = 100, Exam Max = 100
   - (Check System > Assessment Settings to see the configured limits)

3. **Auto-Calculation**
   - System auto-calculates grades using the formula:
     ```
     Final Score = (CA % × Weight) + (Exam % × Weight)
     ```
   - Example: CA=42.5%, Exam=75%, Weights: CA 40%, Exam 60%
     ```
     Final = (42.5 × 0.40) + (75 × 0.60) = 17 + 45 = 62 (B grade)
     ```

4. **Decimal Scores**
   - You can enter decimal scores (18.5, 19.75)
   - Useful for precise marking

5. **Submit for Approval**
   - Marks saved as Draft
   - Admin reviews and approves them
   - After approval, grades appear on student report cards

---

## Example Workflow

**Monday:**
1. Enter Math marks for Class 10A (30 students) ✓ Draft saved
2. Enter English marks for Class 10A (30 students) ✓ Draft saved

**Tuesday:**
1. Review Math marks, make corrections
2. Enter Science marks for Class 10A ✓ Draft saved
3. Enter History marks for Class 10A ✓ Draft saved

**Wednesday:**
1. Click "Submit" on all saved marks
2. Marks sent to Admin for approval

**Next Week:**
1. Admin reviews and approves
2. Grades appear in student portals

---

## Troubleshooting

### ❌ "Enter" button is disabled
**Solution:** Make sure you've selected:
- ✓ Term
- ✓ Class
- ✓ Course

### ❌ "404 Not Found" error
**Solution:** Refresh the page and try again, or:
1. Make sure all three dropdowns have values
2. Check that the course exists for this class

### ❌ Can't find my class in dropdown
**Solution:** 
- Contact Admin to verify the class exists in the system
- Check if students are enrolled in this class

### ❌ Marks not saving
**Solution:**
- Make sure at least one student has an exam score
- Scores must be numeric (numbers only)
- Try saving again

---

## Tips & Best Practices

✅ **Best Practices:**

1. **Enter Test Scores First**
   - Enter Test 1 and Test 2 from your class records
   - Then add Assignments and Participation

2. **Save Often**
   - Save after every few students to avoid losing data
   - Marks in Draft can be edited anytime

3. **Use Decimal Scores**
   - More precise grading (18.5 instead of 18 or 19)

4. **Double-Check Exam Scores**
   - These are the most important
   - Verify against your exam papers before entering

5. **Enter All Courses for a Class**
   - Complete all courses for Class 10A first
   - Then move to next class

6. **Batch Entry**
   - Don't spread entries across days
   - Complete all marks for a term in one session if possible

---

## Summary

**Simple 3-Step Process:**

1. **Select:** Term → Class → Course
2. **Enter:** Test 1, Test 2, Assignment, Participation, Exam
3. **Save:** Click "Save Marks"

**System Does:**
- ✓ Auto-calculates CA percentage
- ✓ Auto-calculates final score
- ✓ Auto-assigns letter grade
- ✓ Auto-adds remark
- ✓ Saves as Draft (editable)
- ✓ Sends to Admin for approval

**Then Admin:**
- ✓ Reviews marks
- ✓ Approves or rejects
- ✓ Publishes to student report cards

---

**That's it! You're ready to enter marks.** 🎓

Questions? Contact your System Administrator.
