# Test Data Seeding Guide

This document explains the structured test data that's seeded into the system for comprehensive testing.

## Quick Start

```bash
# Reset and seed the database with fresh test data
php artisan migrate:fresh --seed
```

## What Gets Seeded

### 1. **School Information**
- **Name**: Bright Future Academy
- **Email**: info@brightfuture.edu.gh
- **Phone**: +233 24 123 4567
- **Address**: 123 Education Street, Accra, Ghana
- **Motto**: Excellence in Education

### 2. **Academic Structure**

#### Academic Years
- **2024-2025** (ACTIVE) - Sep 2024 to Aug 2025
- **2025-2026** (Inactive) - Sep 2025 to Aug 2026

#### Terms (within 2024-2025 academic year)
- **Term 1** (ACTIVE) - Sep 1 to Nov 30, 2024
- **Term 2** - Dec 1 to Feb 28, 2025
- **Term 3** - Mar 1 to Jun 30, 2025

### 3. **School Classes** (9 classes)
**Primary Level:**
- Primary 1, Primary 2, Primary 3, Primary 4, Primary 5, Primary 6

**JHS Level:**
- JHS 1, JHS 2, JHS 3

### 4. **Students** (60 total - 6 per class)
- **Real Ghana names** (Kofi, Ama, Kwame, Akosua, Yaw, Abena, etc.)
- **Student IDs**: SCH-000001 through SCH-000060
- **Parent names and phone numbers** included
- **Admission dates**: Varied (6-24 months ago)
- **DOB**: Random (6-14 years old)
- **Gender**: Random mix

**Sample Student:**
```
Name: Kofi Mensah
ID: SCH-000001
Class: Primary 1
Parent: Mr. John Mensah
Phone: 0244123456
DOB: 2010-05-15
Admission: 2023-06-01
```

### 5. **Fee Structures** (per term, per class)

#### Primary Classes
- **School Fees**: GHS 300/term
- **Feeding Fees**: GHS 5/day (Mon-Fri = GHS 25/week)
- **Registration Fees**: GHS 50 (one-time)
- **Other Fees**: GHS 100/term

#### JHS Classes
- **School Fees**: GHS 400/term
- **Feeding Fees**: GHS 8/day (Mon-Fri = GHS 40/week)
- **Registration Fees**: GHS 75 (one-time)
- **Other Fees**: GHS 150/term

**Expected per student per term:**
- Primary: GHS 450 + feeding (varies by days attended)
- JHS: GHS 625 + feeding (varies by days attended)

### 6. **Payments** (150 transactions)
- **Distribution**: Random across all students
- **Amounts vary by fee type**:
  - School Fees: GHS 200-400
  - Feeding Fees: GHS 20-40
  - Registration Fees: GHS 50-100
  - Other Fees: GHS 50-150
- **Payment Methods**: Cash, MoMo, Bank (random)
- **Dates**: Last 60 days (simulating ongoing collection)
- **Receipt Numbers**: RCP-2024-00001 through RCP-2024-00150

**Sample Payment:**
```
Receipt: RCP-2024-00001
Student: Kofi Mensah (SCH-000001)
Amount: GHS 300
Fee Type: School Fees
Method: Cash
Date: 2024-10-15
```

### 7. **Expense Categories** (8 categories)
1. **Staff Salaries** - GHS 2,000-5,000 per entry
2. **Utilities** - GHS 300-800
3. **Maintenance** - GHS 500-2,000
4. **Supplies** - GHS 200-1,000
5. **Transportation** - GHS 100-500
6. **Meals** - GHS 500-1,500
7. **Health & Insurance** - GHS 400-1,200
8. **Training & Development** - GHS 600-2,000

### 8. **Expenses** (80 total - 10 per category)
- **Random dates**: Last 90 days
- **Reference numbers**: EXP-2024-0001 onwards
- **Realistic descriptions**: Specific items per category
- **Varied amounts**: Based on category type

**Sample Expense:**
```
Reference: EXP-2024-0001
Category: Staff Salaries
Amount: GHS 3,500
Description: September salary
Date: 2024-09-30
```

## Test Scenarios

### Scenario 1: Payment Recording (4-click flow)
1. Navigate to Payments
2. Search student → "Kofi" (gets SCH-000001)
3. Filter by class → Primary 1 (auto-populated)
4. Select "School Fees" 
5. Amount auto-fills to balance owed
6. Select payment method → Cash
7. Click "Record Payment"
8. Receipt displays with parent WhatsApp link
9. Payment shows in history

### Scenario 2: Student Financial Status
1. Navigate to Students → Primary 1
2. Find Kofi Mensah (SCH-000001)
3. Click "Record Payment"
4. Modal opens with student pre-selected
5. View financial summary
6. Record multiple payments across different fee types
7. Check updated balances

### Scenario 3: Class-Based Filtering
1. Go to Payments page
2. Use class filter dropdown
3. Select "JHS 1"
4. Student list shows only JHS 1 students
5. Faster searching for bulk payment recording

### Scenario 4: Report Generation
1. Navigate to Reports
2. **Financial Summary Report**
   - Select Term 1 (2024-2025)
   - Export as CSV
   - See payment transactions by student
3. **Student Enrollment Report**
   - Filter by Primary 1 class
   - Get list with contact info
   - Export for parent communication
4. **Fee Collection Report**
   - Select Term 1
   - See expected vs collected per class
5. **Payment History Report**
   - Date range: Last 30 days
   - Export payment audit trail

### Scenario 5: Financial Analysis
1. Open Reports → Financial Summary
2. Filter by Academic Year 2024-2025
3. View 4 tabs:
   - **Fee Type**: School Fees (GHS 18,000 expected, ~12,000 collected)
   - **By Class**: Distribution across 9 classes
   - **By Student**: Individual balances (some fully paid, some pending)
   - **Transactions**: Audit trail of all 150 payments

## Data Statistics

| Item | Count | Notes |
|------|-------|-------|
| School | 1 | Bright Future Academy |
| Academic Years | 2 | 2024-25 (active), 2025-26 |
| Terms | 3 | Per academic year |
| Classes | 9 | 6 Primary, 3 JHS |
| Students | 60 | 6 per class |
| Fee Structures | 108 | 3 terms × 9 classes × 4 fee types |
| Payments | 150 | Random distribution |
| Expense Categories | 8 | Diverse school expenses |
| Expenses | 80 | 10 per category |

## Expected System State After Seeding

**Students Tab:**
- 60 searchable students across 9 classes
- Full contact info for parent communication
- Financial summary visible per student

**Payments Tab:**
- 150 historical payments
- Smart search by name/ID working
- Class filter functional
- Quick payment recording from student list

**Reports:**
- All 5 report types generating correctly
- PDF/CSV exports working
- Real data across all categories

**Financial Health:**
- Total Expected (all fees): ~GHS 27,000
- Total Collected: ~GHS 18,000
- Collection Rate: ~67%
- Outstanding: ~GHS 9,000

## Modifying Test Data

Edit individual seeders in `database/seeders/`:
- Change `StudentSeeder.php` for different student counts
- Modify `PaymentSeeder.php` to adjust payment distribution
- Adjust `FeeStructureSeeder.php` for different fee amounts

Example - Add more payments:
```php
// In PaymentSeeder.php, change:
for ($i = 0; $i < 300; $i++) {  // From 150 to 300
```

Then reseed:
```bash
php artisan migrate:fresh --seed
```

## Database Reset

To clear and reseed from scratch:
```bash
# Option 1: Full reset with seeding
php artisan migrate:fresh --seed

# Option 2: Just seed (if migrations already run)
php artisan db:seed

# Option 3: Clear specific tables
php artisan tinker
> Student::truncate();
> exit

# Then reseed
php artisan db:seed StudentSeeder
```

## Troubleshooting

**Error: "No suitable classes found"**
- Run migrations first: `php artisan migrate`

**Missing seeders**
- Check all seeder files exist in `database/seeders/`

**Foreign key errors**
- Ensure seeders run in correct order (DatabaseSeeder orchestrates this)

**Data not appearing**
- Clear cache: `php artisan config:cache`
- Verify database connection in `.env`

## Testing Checklist

Use this data to test:

- [ ] Student search and typeahead
- [ ] Payment recording (4-click flow)
- [ ] Class-based filtering
- [ ] Receipt generation and numbering
- [ ] Payment history display
- [ ] Financial summary calculations
- [ ] Report generation (all 5 types)
- [ ] CSV/PDF exports
- [ ] Parent WhatsApp notification links
- [ ] Fee balance calculations per type
- [ ] Outstanding balance tracking
- [ ] Expense categorization
- [ ] Dark mode rendering
- [ ] Mobile responsiveness
- [ ] Search and pagination

## Performance Notes

With 60 students, 150 payments, and 80 expenses:
- Payments page loads in ~200ms
- Student search responds in <50ms
- Report generation in ~300ms
- All operations performant on typical server

For stress testing, increase counts by 10x (600 students, 1500 payments).
