<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Exports\CourseReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SessionReportExport;

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

    public function myReport(Request $request)
    {
        $student = $request->user();

        if ($student->role !== 'student') {
            return response()->json([
                'message' => 'Only students can view this report'
            ], 403);
        }

        $enrollments = Enrollment::with('course.doctor')
            ->where('user_id', $student->id)
            ->get();

        $coursesReport = [];

        $overallTotalSessions = 0;
        $overallPresentCount = 0;

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;

            if (!$course) {
                continue;
            }

            $sessions = $course->sessions;
            $totalSessions = $sessions->count();

            $sessionIds = $sessions->pluck('id');

            $presentCount = AttendanceRecord::where('user_id', $student->id)
                ->whereIn('attendance_session_id', $sessionIds)
                ->where('status', 'present')
                ->count();

            $absentCount = $totalSessions - $presentCount;

            $percentage = $totalSessions > 0
                ? round(($presentCount / $totalSessions) * 100, 2)
                : 0;

            $coursesReport[] = [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'course_code' => $course->code,
                'semester' => $course->semester,
                'level' => $course->level,
                'doctor' => $course->doctor ? [
                    'id' => $course->doctor->id,
                    'name' => $course->doctor->name,
                    'email' => $course->doctor->email,
                ] : null,
                'total_sessions' => $totalSessions,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'attendance_percentage' => $percentage . '%'
            ];

            $overallTotalSessions += $totalSessions;
            $overallPresentCount += $presentCount;
        }

        $overallAbsentCount = $overallTotalSessions - $overallPresentCount;

        $overallPercentage = $overallTotalSessions > 0
            ? round(($overallPresentCount / $overallTotalSessions) * 100, 2)
            : 0;

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'university_code' => $student->university_code,
                'major' => $student->major,
                'level' => $student->level,
            ],
            'courses' => $coursesReport,
            'overall' => [
                'total_sessions' => $overallTotalSessions,
                'present_count' => $overallPresentCount,
                'absent_count' => $overallAbsentCount,
                'attendance_percentage' => $overallPercentage . '%'
            ]
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
    public function exportSessionReport($id)
    {
        $session = \App\Models\AttendanceSession::with('course')->find($id);

        if (!$session) {
            return response()->json([
                'message' => 'Session not found'
            ], 404);
        }

        $fileName = str_replace(' ', '_', $session->course->name)
            . '_Session_' . $session->id . '_Report.xlsx';

        return Excel::download(
            new SessionReportExport($id),
            $fileName
        );
    }
}
