<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\AttendanceRecord;
use App\Exports\CourseReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function courseReport($id)
    {
        $course = Course::with([
            'doctor',
            'sessions.records.user'
        ])->find($id);

        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }

        return response()->json($course);
    }

    public function studentReport($id)
    {
        $records = AttendanceRecord::with('session.course')
            ->where('user_id', $id)
            ->get();

        $present = $records->where('status', 'present')->count();
        $total = $records->count();
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        return response()->json([
            'student_id' => $id,
            'total_sessions' => $total,
            'present_count' => $present,
            'absent_count' => $total - $present,
            'attendance_percentage' => $percentage . '%',
            'records' => $records
        ]);
    }

    public function exportCourseReport($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }

        $fileName = str_replace(' ', '_', $course->name) . '_Report.xlsx';

        return Excel::download(
            new CourseReportExport($id),
            $fileName
        );
    }
}
