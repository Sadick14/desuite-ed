<?php

namespace App\Services;

use App\Models\School;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $school;

    public function __construct()
    {
        $this->school = School::first();
    }

    public function sendSingleSms(array $data): SmsLog
    {
        $smsLog = SmsLog::create([
            'student_id' => $data['student_id'] ?? null,
            'recipient_phone' => $this->formatPhoneNumber($data['phone']),
            'recipient_name' => $data['name'] ?? null,
            'message' => $data['message'],
            'sms_type' => $data['type'] ?? 'general',
            'user_id' => auth()->id(),
        ]);

        try {
            $this->sendViaProvider($smsLog);
            $smsLog->markAsSent();
        } catch (\Exception $e) {
            Log::error('SMS sending failed: '.$e->getMessage());
            $smsLog->markAsFailed($e->getMessage());
        }

        return $smsLog;
    }

    public function sendUsingTemplate(SmsTemplate $template, array $data, ?Student $student = null): SmsLog
    {
        $message = $template->render(array_merge([
            'school_name' => $this->school?->name ?? 'School',
        ], $data));

        return $this->sendSingleSms([
            'student_id' => $student?->id,
            'phone' => $data['phone'],
            'name' => $data['name'] ?? null,
            'message' => $message,
            'type' => $template->type,
        ]);
    }

    public function sendBulkSms(array $recipients, string $message, string $type = 'general'): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            $results[] = $this->sendSingleSms([
                'student_id' => $recipient['student_id'] ?? null,
                'phone' => $recipient['phone'],
                'name' => $recipient['name'] ?? null,
                'message' => $message,
                'type' => $type,
            ]);
        }

        return $results;
    }

    public function sendPaymentConfirmation(Student $student, array $paymentData): SmsLog
    {
        $template = SmsTemplate::findBySlug('payment_confirmation');

        if (! $template) {
            $message = "Dear {$student->parent_name}, payment of GHS {$paymentData['amount']} received for {$student->first_name} {$student->last_name} - {$paymentData['fee_type']}. Receipt: {$paymentData['receipt_number']}. Thank you. {$this->school?->name}";

            return $this->sendSingleSms([
                'student_id' => $student->id,
                'phone' => $student->parent_phone,
                'name' => $student->parent_name,
                'message' => $message,
                'type' => 'payment_confirmation',
            ]);
        }

        return $this->sendUsingTemplate($template, [
            'phone' => $student->parent_phone,
            'name' => $student->parent_name,
            'parent_name' => $student->parent_name,
            'student_name' => "{$student->first_name} {$student->last_name}",
            'amount' => number_format($paymentData['amount'], 2),
            'fee_type' => $paymentData['fee_type'],
            'receipt_number' => $paymentData['receipt_number'],
        ], $student);
    }

    public function sendBalanceReminder(Student $student, array $balanceData): SmsLog
    {
        $template = SmsTemplate::findBySlug('balance_reminder');

        if (! $template) {
            $message = "Dear {$student->parent_name}, {$student->first_name} {$student->last_name} has an outstanding balance of GHS {$balanceData['balance']} for {$balanceData['term']}. Please make payment soon. Thank you. {$this->school?->name}";

            return $this->sendSingleSms([
                'student_id' => $student->id,
                'phone' => $student->parent_phone,
                'name' => $student->parent_name,
                'message' => $message,
                'type' => 'balance_reminder',
            ]);
        }

        return $this->sendUsingTemplate($template, [
            'phone' => $student->parent_phone,
            'name' => $student->parent_name,
            'parent_name' => $student->parent_name,
            'student_name' => "{$student->first_name} {$student->last_name}",
            'balance' => number_format($balanceData['balance'], 2),
            'term' => $balanceData['term'],
        ], $student);
    }

    public function sendBulkBalanceReminders(array $studentsWithBalances): array
    {
        $results = [];

        foreach ($studentsWithBalances as $item) {
            $results[] = $this->sendBalanceReminder($item['student'], $item['balance_data']);
        }

        return $results;
    }

    public function sendGeneralAnnouncement(array $recipients, string $announcement): array
    {
        $template = SmsTemplate::findBySlug('general_announcement');
        $message = $template ? $template->render([
            'announcement' => $announcement,
            'school_name' => $this->school?->name ?? 'School',
        ]) : "Dear Parents, {$announcement}. {$this->school?->name}";

        return $this->sendBulkSms($recipients, $message, 'announcement');
    }

    protected function formatPhoneNumber($phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10 && strpos($phone, '0') === 0) {
            return '233'.substr($phone, 1);
        }

        if (strlen($phone) === 9 && ! strpos($phone, '233') === 0) {
            return '233'.$phone;
        }

        if (strlen($phone) === 12 && strpos($phone, '233') === 0) {
            return $phone;
        }

        return $phone;
    }

    protected function sendViaProvider(SmsLog $smsLog): void
    {
        $provider = config('sms.default', 'log');

        switch ($provider) {
            case 'hubtel':
                $this->sendViaHubtel($smsLog);
                break;
            case 'mnotify':
                $this->sendViaMNotify($smsLog);
                break;
            case 'twilio':
                $this->sendViaTwilio($smsLog);
                break;
            case 'log':
            default:
                $this->sendViaLog($smsLog);
                break;
        }
    }

    protected function sendViaLog(SmsLog $smsLog): void
    {
        Log::info('SMS Sent (Logged Only)', [
            'to' => $smsLog->recipient_phone,
            'message' => $smsLog->message,
        ]);
    }

    protected function sendViaHubtel(SmsLog $smsLog): void
    {
        $apiKey = config('sms.providers.hubtel.api_key');
        $apiSecret = config('sms.providers.hubtel.api_secret');
        $senderId = config('sms.providers.hubtel.sender_id', 'SCHOOL');

        $response = Http::withBasicAuth($apiKey, $apiSecret)
            ->post('https://smsc.hubtel.com/v1/messages/send', [
                'From' => $senderId,
                'To' => $smsLog->recipient_phone,
                'Content' => $smsLog->message,
            ]);

        if (! $response->successful()) {
            throw new \Exception('Hubtel SMS failed: '.$response->body());
        }
    }

    protected function sendViaMNotify(SmsLog $smsLog): void
    {
        $apiKey = config('sms.providers.mnotify.api_key');
        $senderId = config('sms.providers.mnotify.sender_id', 'SCHOOL');

        $response = Http::post('https://api.mnotify.com/api/sms/quick', [
            'key' => $apiKey,
            'to' => $smsLog->recipient_phone,
            'msg' => $smsLog->message,
            'sender_id' => $senderId,
        ]);

        if (! $response->successful()) {
            throw new \Exception('MNotify SMS failed: '.$response->body());
        }
    }

    protected function sendViaTwilio(SmsLog $smsLog): void
    {
        $sid = config('sms.providers.twilio.sid');
        $token = config('sms.providers.twilio.token');
        $from = config('sms.providers.twilio.from');

        $response = Http::withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $smsLog->recipient_phone,
                'Body' => $smsLog->message,
            ]);

        if (! $response->successful()) {
            throw new \Exception('Twilio SMS failed: '.$response->body());
        }
    }
}
