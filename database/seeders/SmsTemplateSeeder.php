<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmsTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Payment Confirmation',
                'slug' => 'payment_confirmation',
                'message' => 'Dear {parent_name}, payment of GHS {amount} received for {student_name} - {fee_type}. Receipt: {receipt_number}. Thank you. {school_name}',
                'type' => 'payment',
                'variables' => json_encode(['parent_name', 'student_name', 'amount', 'fee_type', 'receipt_number', 'school_name']),
                'is_active' => true,
            ],
            [
                'name' => 'Balance Reminder',
                'slug' => 'balance_reminder',
                'message' => 'Dear {parent_name}, {student_name} has an outstanding balance of GHS {balance} for {term}. Please make payment soon. Thank you. {school_name}',
                'type' => 'reminder',
                'variables' => json_encode(['parent_name', 'student_name', 'balance', 'term', 'school_name']),
                'is_active' => true,
            ],
            [
                'name' => 'Fee Due Reminder',
                'slug' => 'fee_due_reminder',
                'message' => 'Dear {parent_name}, school fees for {term} are now due. Total: GHS {total_amount}. Please make payment promptly. {school_name}',
                'type' => 'reminder',
                'variables' => json_encode(['parent_name', 'term', 'total_amount', 'school_name']),
                'is_active' => true,
            ],
            [
                'name' => 'General Announcement',
                'slug' => 'general_announcement',
                'message' => 'Dear Parents, {announcement}. {school_name}',
                'type' => 'general',
                'variables' => json_encode(['announcement', 'school_name']),
                'is_active' => true,
            ],
            [
                'name' => 'Attendance Alert',
                'slug' => 'attendance_alert',
                'message' => 'Dear {parent_name}, {student_name} was marked {status} on {date}. Please ensure regular attendance. {school_name}',
                'type' => 'attendance',
                'variables' => json_encode(['parent_name', 'student_name', 'status', 'date', 'school_name']),
                'is_active' => true,
            ],
            [
                'name' => 'Welcome New Student',
                'slug' => 'welcome_student',
                'message' => 'Welcome {student_name} to {school_name}! We are excited to have you. Student ID: {student_id}. {school_name}',
                'type' => 'general',
                'variables' => json_encode(['student_name', 'student_id', 'school_name']),
                'is_active' => true,
            ],
            [
                'name' => 'Exam Schedule',
                'slug' => 'exam_schedule',
                'message' => 'Dear {parent_name}, exams start on {date}. Please ensure {student_name} is prepared. {school_name}',
                'type' => 'announcement',
                'variables' => json_encode(['parent_name', 'student_name', 'date', 'school_name']),
                'is_active' => true,
            ],
            [
                'name' => 'Report Card Ready',
                'slug' => 'report_card_ready',
                'message' => 'Dear {parent_name}, {student_name}\'s report card is ready for collection. Please visit the school. {school_name}',
                'type' => 'announcement',
                'variables' => json_encode(['parent_name', 'student_name', 'school_name']),
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('sms_templates')->updateOrInsert(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
