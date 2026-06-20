<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromArray;

class CourseReportExport implements FromArray
{
    protected $courseId;

    public function __construct($courseId)
    {
        $this->courseId = $courseId;
    }

    public function array(): array
    {
        $rows = [];

        $course = Course::with('sessions')->find($this->courseId);

        $rows[] = ['Course Name', $course->name];
        $rows[] = ['Course Code', $course->code];
        $rows[] = ['Total Sessions', $course->sessions->count()];
        $rows[] = [];

        $rows[] = [
            'University Code',
            'Full Name'
        ];

        $students = Enrollment::with('user')
            ->where('course_id', $this->courseId)
            ->get();

        foreach ($students as $enrollment) {
            $student = $enrollment->user;

            $rows[] = [
                $student->university_code,
                $student->name
            ];
        }

        return $rows;
    }
}
