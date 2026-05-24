<?php

namespace App\Exports;

use App\Models\Enrollment;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Maatwebsite\Excel\Concerns\FromArray;

class SessionReportExport implements FromArray
{
    protected $sessionId;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function array(): array
    {
        $rows = [];

        $session = AttendanceSession::with('course')->find($this->sessionId);

        $rows[] = ['Course Name', $session->course->name];
        $rows[] = ['Course Code', $session->course->code];
        $rows[] = ['Session ID', $session->id];
        $rows[] = ['Status', $session->status];
        $rows[] = [];

        $rows[] = [
            'University Code',
            'Full Name',
            'Status'
        ];

        $students = Enrollment::with('user')
            ->where('course_id', $session->course_id)
            ->get();

        $presentStudents = [];
        $absentStudents = [];

        foreach ($students as $enrollment) {
            $student = $enrollment->user;

            $present = AttendanceRecord::where('user_id', $student->id)
                ->where('attendance_session_id', $this->sessionId)
                ->exists();

            $studentRow = [
                $student->university_code,
                $student->name,
                $present ? 'Present' : 'Absent'
            ];

            if ($present) {
                $presentStudents[] = $studentRow;
            } else {
                $absentStudents[] = $studentRow;
            }
        }

        foreach ($presentStudents as $student) {
            $rows[] = $student;
        }

        foreach ($absentStudents as $student) {
            $rows[] = $student;
        }

        return $rows;
    }
}
