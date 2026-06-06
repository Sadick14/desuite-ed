# Database Structure & Test Data

## Data Schema Overview

```
ACADEMIC_YEARS (2)
├── id: 1 → 2024-2025 (ACTIVE)
└── id: 2 → 2025-2026

TERMS (3)
├── id: 1 → Term 1 (Sep-Nov) [ACTIVE]
├── id: 2 → Term 2 (Dec-Feb)
└── id: 3 → Term 3 (Mar-Jun)
   └── FK: academic_year_id

SCHOOL_CLASSES (9)
├── P1, P2, P3, P4, P5, P6
├── JHS 1, JHS 2, JHS 3
└── All marked with level (Primary/JHS)

STUDENTS (60)
├── 6 per class
├── Student IDs: SCH-000001 to SCH-000060
├── Real names (Ghana names)
├── Parent contact info
├── Admission dates varied
└── FK: school_class_id

FEE_STRUCTURES (108 total)
├── 3 terms × 9 classes × 4 fee types
├── Primary: School(300), Feeding(5/day), Reg(50), Other(100)
├── JHS: School(400), Feeding(8/day), Reg(75), Other(150)
└── FK: term_id, school_class_id

PAYMENTS (150)
├── Random students (multiple per student)
├── Random amounts by fee type
├── Receipt numbers: RCP-2024-00001 to 150
├── 3 payment methods (cash, momo, bank)
├── Dates: Last 60 days
└── FK: student_id, term_id, user_id

EXPENSE_CATEGORIES (8)
├── Staff Salaries
├── Utilities
├── Maintenance
├── Supplies
├── Transportation
├── Meals
├── Health & Insurance
└── Training & Development

EXPENSES (80 total)
├── 10 per category
├── Amounts: GHS 100-5000 depending on type
├── Dates: Last 90 days
├── Reference: EXP-2024-0001 onwards
└── FK: expense_category_id, user_id

USERS (1 admin)
├── Name: Admin User
├── Email: admin@school.local
└── Password: password (bcrypted)

SCHOOL (1)
├── Name: Bright Future Academy
├── Email, phone, address
├── Motto: Excellence in Education
└── Contact info for communication
```

---

## Detailed Tables

### ACADEMIC_YEARS
```sql
CREATE TABLE academic_years (
  id INT PRIMARY KEY,
  name VARCHAR(50) -- "2024-2025"
  start_date DATE,
  end_date DATE,
  is_active BOOLEAN,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA:
1 | 2024-2025 | 2024-09-01 | 2025-08-31 | 1 | ...
2 | 2025-2026 | 2025-09-01 | 2026-08-31 | 0 | ...
```

### TERMS
```sql
CREATE TABLE terms (
  id INT PRIMARY KEY,
  name VARCHAR(50), -- "Term 1"
  academic_year_id INT FK,
  start_date DATE,
  end_date DATE,
  is_active BOOLEAN,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA:
1 | Term 1 | 1 | 2024-09-01 | 2024-11-30 | 1
2 | Term 2 | 1 | 2024-12-01 | 2025-02-28 | 0
3 | Term 3 | 1 | 2025-03-01 | 2025-06-30 | 0
```

### SCHOOL_CLASSES
```sql
CREATE TABLE school_classes (
  id INT PRIMARY KEY,
  name VARCHAR(50), -- "Primary 1"
  level VARCHAR(20), -- "Primary" or "JHS"
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA (9 records):
1 | Primary 1 | Primary
2 | Primary 2 | Primary
...
7 | JHS 1 | JHS
8 | JHS 2 | JHS
9 | JHS 3 | JHS
```

### STUDENTS
```sql
CREATE TABLE students (
  id INT PRIMARY KEY,
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  student_id VARCHAR(50) UNIQUE, -- "SCH-000001"
  school_class_id INT FK,
  gender ENUM('male', 'female'),
  date_of_birth DATE,
  parent_name VARCHAR(100),
  parent_phone VARCHAR(20),
  address TEXT,
  admission_date DATE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA:
id | first_name | last_name | student_id  | class_id | gender | dob        | parent_name   | parent_phone
1  | Kofi       | Mensah    | SCH-000001  | 1        | male   | 2010-05-15 | Mr. John M.   | 0244123456
2  | Ama        | Owusu     | SCH-000002  | 1        | female | 2011-08-22 | Mrs. Mary O.  | 0244123457
...
60 | Benjamin   | Appiah    | SCH-000060  | 9        | male   | 2009-03-10 | Mr. David A.  | 0244123465
```

### FEE_STRUCTURES
```sql
CREATE TABLE fee_structures (
  id INT PRIMARY KEY,
  term_id INT FK,
  school_class_id INT FK,
  fee_type ENUM(
    'school_fees',
    'feeding_fees',
    'registration_fees',
    'others'
  ),
  amount DECIMAL(10,2),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA:
id | term_id | class_id | fee_type            | amount
1  | 1       | 1        | school_fees         | 300.00
2  | 1       | 1        | feeding_fees        | 5.00
3  | 1       | 1        | registration_fees   | 50.00
4  | 1       | 1        | others              | 100.00
...
108| 3       | 9        | others              | 150.00
```

**Analysis by Class:**
- **Primary Students**: GHS 450 + feeding (GHS 5/day × school days)
- **JHS Students**: GHS 625 + feeding (GHS 8/day × school days)

### PAYMENTS
```sql
CREATE TABLE payments (
  id INT PRIMARY KEY,
  student_id INT FK,
  term_id INT FK,
  amount DECIMAL(10,2),
  payment_type ENUM(
    'school_fees',
    'feeding_fees',
    'registration_fees',
    'others'
  ),
  payment_method ENUM('cash', 'momo', 'bank'),
  receipt_number VARCHAR(50) UNIQUE, -- "RCP-2024-00001"
  payment_date DATE,
  user_id INT FK,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA (150 total):
id  | student_id | term_id | amount | payment_type    | method | receipt_number   | payment_date
1   | 15         | 1       | 300.00 | school_fees     | cash   | RCP-2024-00001   | 2024-10-15
2   | 3          | 1       | 25.00  | feeding_fees    | momo   | RCP-2024-00002   | 2024-10-16
3   | 42         | 1       | 100.00 | others          | bank   | RCP-2024-00003   | 2024-10-17
...
150 | 28         | 1       | 400.00 | school_fees     | cash   | RCP-2024-00150   | 2024-12-13
```

**Statistics:**
- Total Amount: ~GHS 18,000
- Average Payment: ~GHS 120
- School Fees dominate (40%)
- Good distribution across methods

### EXPENSE_CATEGORIES
```sql
CREATE TABLE expense_categories (
  id INT PRIMARY KEY,
  name VARCHAR(100),
  description TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA (8):
1 | Staff Salaries          | Salaries and wages...
2 | Utilities               | Electricity, water...
3 | Maintenance             | Building repairs...
4 | Supplies                | Office materials...
5 | Transportation          | Vehicle fuel...
6 | Meals                   | Food supplies...
7 | Health & Insurance      | Insurance...
8 | Training & Development  | Staff training...
```

### EXPENSES
```sql
CREATE TABLE expenses (
  id INT PRIMARY KEY,
  expense_category_id INT FK,
  amount DECIMAL(10,2),
  description TEXT,
  date DATE,
  reference VARCHAR(50),
  user_id INT FK,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA (80 total):
id | category_id | amount    | description          | date       | reference
1  | 1           | 3500.00   | September salary     | 2024-09-30 | EXP-2024-0001
2  | 2           | 450.00    | Electricity bill     | 2024-10-05 | EXP-2024-0002
3  | 3           | 1200.00   | Roof repair          | 2024-10-10 | EXP-2024-0003
...
80 | 8           | 1500.00   | Staff workshop       | 2024-12-01 | EXP-2024-0080
```

**Budget Estimate:**
- Salaries: ~GHS 35,000 (largest expense)
- Other categories: ~GHS 25,000
- Total monthly: ~GHS 6,000-8,000

### SCHOOL
```sql
CREATE TABLE schools (
  id INT PRIMARY KEY,
  name VARCHAR(200),
  email VARCHAR(100),
  phone VARCHAR(20),
  address TEXT,
  motto VARCHAR(200),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA (1):
1 | Bright Future Academy | info@brightfuture.edu.gh | 
  | +233 24 123 4567      | 123 Education St, Accra   | Excellence in Education
```

### USERS
```sql
CREATE TABLE users (
  id INT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255), -- hashed
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

SAMPLE DATA (1):
1 | Admin User | admin@school.local | NULL | [bcrypted] | ...
```

---

## Relationships (Entity Diagram)

```
ACADEMIC_YEAR (1) ──┐
                    │
                 (has many)
                    │
                    └──→ TERM (3) ──┐
                                    │
                                 (has many)
                                    │
                ┌─────────────────────┼──────────────────┐
                │                     │                  │
            (for)                 (for)             (for)
                │                     │                  │
                ↓                     ↓                  ↓
          FEE_STRUCTURE ──→ SCHOOL_CLASS ←── STUDENT ──→ PAYMENT
          (108 records)      (9 classes)     (60)        (150)
                                                           │
                                                           └──→ USER
                                                           
EXPENSE_CATEGORY (8) ──→ EXPENSE (80) ──→ USER
```

---

## Query Examples

### Find Student with Financial Status
```sql
SELECT 
  s.*,
  COUNT(DISTINCT p.id) as payment_count,
  SUM(p.amount) as total_paid,
  SUM(fs.amount) as expected_fees,
  (SUM(fs.amount) - SUM(p.amount)) as balance_due
FROM students s
LEFT JOIN payments p ON s.id = p.student_id
LEFT JOIN fee_structures fs ON s.school_class_id = fs.school_class_id
WHERE s.id = 1
GROUP BY s.id;
```

### Class Financial Summary
```sql
SELECT 
  sc.name as class_name,
  COUNT(DISTINCT s.id) as student_count,
  SUM(p.amount) as total_collected,
  SUM(fs.amount) as expected_revenue
FROM school_classes sc
LEFT JOIN students s ON s.school_class_id = sc.id
LEFT JOIN payments p ON s.id = p.student_id
LEFT JOIN fee_structures fs ON s.school_class_id = fs.school_class_id
WHERE fs.term_id = 1
GROUP BY sc.id;
```

### Fee Type Collection
```sql
SELECT 
  fee_type,
  COUNT(*) as transaction_count,
  SUM(amount) as total_collected,
  AVG(amount) as avg_payment
FROM payments
WHERE term_id = 1
GROUP BY fee_type;
```

### Expense Summary
```sql
SELECT 
  ec.name,
  SUM(e.amount) as total_spent,
  COUNT(e.id) as transaction_count,
  AVG(e.amount) as avg_amount
FROM expenses e
JOIN expense_categories ec ON e.expense_category_id = ec.id
WHERE MONTH(e.date) = 10 AND YEAR(e.date) = 2024
GROUP BY ec.id;
```

---

## Indexes (for Performance)

```sql
-- Student lookups
CREATE INDEX idx_student_class ON students(school_class_id);
CREATE INDEX idx_student_id_code ON students(student_id);

-- Payment queries
CREATE INDEX idx_payment_student ON payments(student_id);
CREATE INDEX idx_payment_term ON payments(term_id);
CREATE INDEX idx_payment_date ON payments(payment_date);

-- Report filtering
CREATE INDEX idx_term_academic_year ON terms(academic_year_id);
CREATE INDEX idx_fee_structure_term_class ON fee_structures(term_id, school_class_id);

-- Expense tracking
CREATE INDEX idx_expense_category ON expenses(expense_category_id);
CREATE INDEX idx_expense_date ON expenses(date);
```

---

## Data Integrity Constraints

```sql
-- Foreign Keys
ALTER TABLE terms ADD CONSTRAINT fk_term_academic_year 
  FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE;

ALTER TABLE students ADD CONSTRAINT fk_student_class 
  FOREIGN KEY (school_class_id) REFERENCES school_classes(id) ON DELETE RESTRICT;

ALTER TABLE fee_structures ADD CONSTRAINT fk_fee_term 
  FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE;

ALTER TABLE fee_structures ADD CONSTRAINT fk_fee_class 
  FOREIGN KEY (school_class_id) REFERENCES school_classes(id) ON DELETE CASCADE;

ALTER TABLE payments ADD CONSTRAINT fk_payment_student 
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE;

ALTER TABLE payments ADD CONSTRAINT fk_payment_term 
  FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE;

ALTER TABLE payments ADD CONSTRAINT fk_payment_user 
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE expenses ADD CONSTRAINT fk_expense_category 
  FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id) ON DELETE RESTRICT;

ALTER TABLE expenses ADD CONSTRAINT fk_expense_user 
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Unique Constraints
ALTER TABLE students ADD CONSTRAINT unique_student_id UNIQUE(student_id);
ALTER TABLE payments ADD CONSTRAINT unique_receipt_number UNIQUE(receipt_number);
ALTER TABLE academic_years ADD CONSTRAINT unique_year_name UNIQUE(name);

-- Check Constraints
ALTER TABLE payments ADD CONSTRAINT check_amount CHECK (amount > 0);
ALTER TABLE expenses ADD CONSTRAINT check_expense_amount CHECK (amount > 0);
ALTER TABLE fee_structures ADD CONSTRAINT check_fee_amount CHECK (amount > 0);
```

---

## Summary Statistics

| Entity | Count | Purpose |
|--------|-------|---------|
| Academic Years | 2 | Multi-year support |
| Terms | 3 | Semester-based tracking |
| Classes | 9 | 3 educational levels |
| Students | 60 | Balanced class size |
| Fee Structures | 108 | 3 terms × 9 classes |
| Payments | 150 | Realistic transaction volume |
| Expense Categories | 8 | Comprehensive budget breakdown |
| Expenses | 80 | Realistic monthly spending |
| **Total Records** | **420** | **Full system testing** |

---

## Testing with This Data

### Payment Flow Validation
- Verify 60 students all searchable
- Test 4-click recording with real data
- Generate receipts from 150 payments
- Export payment history

### Financial Accuracy
- Calculate expected fees per student (GHS 300-625 + feeding)
- Verify balance calculations
- Check collection rates by class
- Audit outstanding balances

### Report Generation
- Generate all 5 report types
- Export 150 payments to CSV
- Export 60 students to CSV
- Export 80 expenses to CSV
- Generate PDF reports

### Performance Testing
- Student search with 60 records
- Filter by 9 classes
- Sort by 15+ columns
- Export 150+ records
- Generate reports in <500ms

---

All data is realistic, interconnected, and ready for comprehensive testing!
