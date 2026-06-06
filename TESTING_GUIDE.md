# Testing Guide - School Management System

Quick guide to test the fully-built school management system.

## Setup

### 1. Seed the Database
```bash
php artisan migrate:fresh --seed
```

This creates:
- **60 students** across 9 classes (Primary 1-6, JHS 1-3)
- **150 payments** with realistic data
- **80 expenses** across 8 categories
- **3 terms** (active = Term 1)
- Complete fee structures

### 2. Login
- **Email**: admin@school.local
- **Password**: password (default from factory)

---

## Core Features to Test

### 🎯 Quick Payment Recording (4 clicks)
**Path**: Sidebar → Core → Payments

**Test Case 1: From Payments Index**
1. Click "Record Payment"
2. Type "Kofi" in student search
3. Click result → auto-fills student
4. Select "School Fees" card
5. Amount auto-fills to balance
6. Select payment method (Cash/MoMo/Bank)
7. Click "Record Payment"
8. Receipt displays with receipt number
9. Click "Print" → prints receipt only

**Test Case 2: From Student List**
1. Sidebar → Core → Students
2. Find student in table
3. Click "Payment" action button
4. Modal opens → student pre-selected
5. Record payment
6. Verify in Payment History tab

**Test Case 3: Class-Based Filtering**
1. Payments page → click "Filter by Class"
2. Select "Primary 1"
3. Student list shows only P1 students
4. Switch to "JHS 1" → instant filter

---

### 📊 Financial Reports
**Path**: Sidebar → Finance → Reports

**Test Case 1: Financial Summary**
1. Select "Financial Summary" report type
2. Choose filter: "Term 1 (2024-2025)"
3. Click "Generate Report"
4. View 4 tabs:
   - **Fee Type Summary** → see school_fees vs feeding_fees vs registration
   - **By Class** → compare P1 vs JHS 1 collection rates
   - **By Student** → individual balances
   - **Transactions** → all 150 payments
5. Download as CSV → opens in Excel/Sheets
6. Download as PDF → use browser print dialog

**Test Case 2: Student Enrollment**
1. Select "Student Enrollment" report type
2. Filter by Class: "JHS 2"
3. Generate Report
4. Export as CSV
5. Shows all 6 JHS 2 students with contact info

**Test Case 3: Fee Collection**
1. Select "Fee Collection" report type
2. Select Academic Year: "2024-2025"
3. Generate Report
4. See fee structures for all classes
5. Export to CSV for accounting

**Test Case 4: Payment History**
1. Select "Payment History" report type
2. Set date range: Last 30 days
3. Generate Report
4. See transaction audit trail
5. Export as CSV for reconciliation

**Test Case 5: Expense Report**
1. Select "Expense Report" report type
2. Filter by Category: "Staff Salaries"
3. Set date range: Last 90 days
4. See all 10 salary entries
5. Export for accounting approval

---

### 👥 Student Management
**Path**: Sidebar → Core → Students

**Test Case 1: View Student Profile**
1. Click any student name
2. See personal info, parent contact
3. View financial summary (expected vs paid)
4. See payment history

**Test Case 2: Financial Breakdown by Fee Type**
1. Student Show page → Financial Summary tab
2. See breakdown:
   - School Fees: GHS 300 expected, X paid, Y balance
   - Feeding Fees: GHS 5/day rate (shows GHS 25/week)
   - Registration Fees: GHS 50
   - Other Fees: GHS 100
3. Progress bar shows % paid overall

**Test Case 3: Payment History Tab**
1. Student Show page → Payment History
2. See all payments this student made
3. Filter by fee type or date (if implemented)
4. Search for specific receipt

**Test Case 4: Register New Student**
1. Click "Register Student" button
2. Fill form:
   - Name: Test Student
   - Class: Primary 1
   - Gender: Male/Female
   - DOB: 2010-05-15
   - Parent: Test Parent
   - Phone: 0244999999
3. Click Submit
4. Appears in student list instantly

---

### 📋 Fee Structures
**Path**: Sidebar → Finance → Fee Structures

**Test Case 1: View Current Structures**
1. Shows fees per term, per class
2. Filter by term
3. See amounts for all 4 fee types:
   - School Fees (fixed)
   - Feeding Fees (daily rate)
   - Registration Fees
   - Other Fees
4. Feeding fee shows guidance: "GHS 5/day = GHS 25/week"

**Test Case 2: Edit Fee Structure**
1. Click edit on a structure
2. Change amount
3. Save
4. Verify on fee collection report

---

### 💰 Expense Management
**Path**: Sidebar → Core → Expenses

**Test Case 1: View All Expenses**
1. See list of 80 expenses
2. Grouped by category
3. Shows amount, date, description
4. Search by category or description

**Test Case 2: Record New Expense**
1. Click "Add Expense"
2. Select category: "Maintenance"
3. Enter amount: 500
4. Description: "Repair classroom roof"
5. Date: Today
6. Submit
7. Appears in list with reference number

**Test Case 3: View Expenses Report**
1. Go to Reports → Expense Report
2. Filter by "Utilities"
3. Generate
4. See all electricity/water bills
5. Export for budget planning

---

### 📈 Dashboard
**Path**: Sidebar → Core → Dashboard

**Test Case 1: Overview Metrics**
1. See summary cards:
   - Total students: 60
   - Total collections this term
   - Outstanding balance
   - Recent payments

**Test Case 2: Quick Actions**
1. Recent payments listed
2. Click to view receipt or student profile

---

## Test Data Reference

### Key Test Student
- **Name**: Kofi Mensah
- **ID**: SCH-000001
- **Class**: Primary 1
- **Parent**: Mr. John Mensah
- **Phone**: 0244123456
- **Expected Fees**: GHS 450 + feeding

### Payment Examples
- **School Fees**: GHS 200-400
- **Feeding Fees**: GHS 20-40 (5×GHS 5 days = GHS 25/week)
- **Registration**: GHS 50-100
- **Others**: GHS 50-150

### Term Info
- **Active Term**: Term 1 (2024-2025)
- **Start Date**: 2024-09-01
- **End Date**: 2024-11-30

---

## Browser Testing Checklist

### Desktop (Chrome/Firefox)
- [ ] All forms responsive
- [ ] Tables scroll properly
- [ ] Modals display centered
- [ ] Buttons hover states work
- [ ] Dark mode toggle functions

### Mobile (Responsive)
- [ ] Student list shows as cards on mobile
- [ ] Payment modal stacks fields vertically
- [ ] Tables scroll horizontally
- [ ] Buttons are tap-friendly
- [ ] Search works on touch

### Dark Mode
- [ ] All pages have dark variants
- [ ] Text contrast meets accessibility
- [ ] Charts/tables readable
- [ ] Icons visible

---

## Performance Benchmarks

With 60 students, 150 payments, 80 expenses:

| Operation | Target | Expected |
|-----------|--------|----------|
| Page Load | <1s | ~200ms |
| Student Search | <100ms | ~30ms |
| Payment Recording | <500ms | ~100ms |
| Report Generation | <2s | ~300ms |
| CSV Export | <500ms | ~200ms |

---

## Troubleshooting During Testing

### Data Looks Wrong
```bash
# Clear cache and reseed
php artisan config:cache
php artisan migrate:fresh --seed
```

### Student Search Not Working
- Check Payments/Index.vue has `uniqueClasses` computed property
- Verify `selectedClassFilter` is reset on modal open

### Receipt Numbers Missing
- Ensure PaymentController line 40 sets receipt_number AFTER validation
- Check receipt_number field in Payment model is in $fillable

### Reports Show No Data
- Verify Term 1 is marked as active
- Check ReportController filtering logic
- Ensure students have payments in selected term

### Payment Amount Auto-fill Not Working
- Check `feeCardsWithBalance` computed property
- Verify FeeStructure query includes correct term_id

---

## Feature Completion Checklist

- [x] Student management (CRUD)
- [x] Payment recording (4-click flow)
- [x] Smart student search with typeahead
- [x] Class-based filtering
- [x] Financial summary per student
- [x] Receipt generation and printing
- [x] Parent WhatsApp notification links
- [x] Payment history tracking
- [x] Fee structure management
- [x] Multiple fee types (school, feeding, registration, others)
- [x] Feeding fee as daily rate (Monday-Friday)
- [x] Quick payment from student table
- [x] Quick payment from student detail page
- [x] Expense tracking and categorization
- [x] Multi-module report generation (5 types)
- [x] CSV/PDF export
- [x] Dark mode support
- [x] Mobile responsive design
- [x] Comprehensive test data (60 students, 150 payments, 80 expenses)

---

## Next Steps (Optional Enhancements)

1. **Excel Export** - Install `laravel/excel` for XLSX generation
2. **Chart Reports** - Add Chart.js for visual fee collection trends
3. **SMS Alerts** - Parent payment reminder SMS via Twilio
4. **Bulk Operations** - Waive fees, bulk payment recording
5. **Fee Discounts** - Apply percentage/flat discounts per student
6. **Advanced Filtering** - Payment status (pending/partial/paid)
7. **Audit Log Viewer** - Track all system changes

---

## Support

If features aren't working:
1. Check git branch is up to date
2. Run `composer install && npm install`
3. Run `php artisan migrate:fresh --seed`
4. Check browser console for JS errors
5. Check Laravel logs: `storage/logs/laravel.log`

Test data is comprehensive and ready for production-like scenarios!
