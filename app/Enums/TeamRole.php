<?php

namespace App\Enums;

enum TeamRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';
    case Parent = 'parent';
    case Member = 'member';

    /**
     * Get the display label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Administrator',
            self::Teacher => 'Teacher',
            self::Student => 'Student',
            self::Parent => 'Parent/Guardian',
            self::Member => 'Member',
        };
    }

    /**
     * Get all the permissions for this role.
     *
     * @return array<TeamPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner => TeamPermission::cases(),
            self::Admin => [
                TeamPermission::UpdateTeam,
                TeamPermission::CreateInvitation,
                TeamPermission::CancelInvitation,
                TeamPermission::ManageStudents,
                TeamPermission::ViewStudents,
                TeamPermission::ManageCourses,
                TeamPermission::ViewCourses,
                TeamPermission::ManageExams,
                TeamPermission::ViewExams,
                TeamPermission::ManageGrades,
                TeamPermission::ViewGrades,
                TeamPermission::ManageAttendance,
                TeamPermission::ViewAttendance,
                TeamPermission::ManagePayments,
                TeamPermission::ViewPayments,
                TeamPermission::ManageExpenses,
                TeamPermission::ViewExpenses,
                TeamPermission::SendSms,
                TeamPermission::ViewSms,
                TeamPermission::GenerateReports,
                TeamPermission::ViewReports,
            ],
            self::Teacher => [
                TeamPermission::ViewStudents,
                TeamPermission::ViewCourses,
                TeamPermission::ViewExams,
                TeamPermission::ManageGrades,
                TeamPermission::ViewGrades,
                TeamPermission::ManageAttendance,
                TeamPermission::ViewAttendance,
                TeamPermission::ViewReports,
            ],
            self::Student => [
                TeamPermission::ViewStudents,
                TeamPermission::ViewCourses,
                TeamPermission::ViewExams,
                TeamPermission::ViewGrades,
                TeamPermission::ViewAttendance,
                TeamPermission::ViewReports,
            ],
            self::Parent => [
                TeamPermission::ViewStudents,
                TeamPermission::ViewGrades,
                TeamPermission::ViewAttendance,
                TeamPermission::ViewReports,
            ],
            self::Member => [
                TeamPermission::ViewStudents,
                TeamPermission::ViewCourses,
            ],
        };
    }

    /**
     * Determine if the role has the given permission.
     */
    public function hasPermission(TeamPermission $permission): bool
    {
        return in_array($permission, $this->permissions());
    }

    /**
     * Get the hierarchy level for this role.
     * Higher numbers indicate higher privileges.
     */
    public function level(): int
    {
        return match ($this) {
            self::Owner => 6,
            self::Admin => 5,
            self::Teacher => 4,
            self::Member => 3,
            self::Parent => 2,
            self::Student => 1,
        };
    }

    /**
     * Check if this role is at least as privileged as another role.
     */
    public function isAtLeast(TeamRole $role): bool
    {
        return $this->level() >= $role->level();
    }

    /**
     * Get the roles that can be assigned to team members (excludes Owner).
     *
     * @return array<array{value: string, label: string}>
     */
    public static function assignable(): array
    {
        return collect(self::cases())
            ->filter(fn (self $role) => $role !== self::Owner)
            ->map(fn (self $role) => ['value' => $role->value, 'label' => $role->label()])
            ->values()
            ->toArray();
    }
}
