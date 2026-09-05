<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PerformanceReportController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StudentDirectoryController;
use App\Http\Controllers\Api\StudentProgressController;
use App\Http\Controllers\Api\TeacherReviewController;
use App\Http\Controllers\Api\WorksheetAssignmentController;
use App\Http\Controllers\Api\WorksheetBundleController;
use App\Http\Controllers\Api\WorksheetController;
use App\Http\Controllers\Api\WorksheetSubmissionController;
use App\Http\Controllers\Api\LmsFeatureController;
use App\Http\Controllers\Api\MeharaPlatformController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('demo-status', [AuthController::class, 'demoStatus'])->middleware('throttle:30,1');
    Route::post('demo-login', [AuthController::class, 'demoLogin'])->middleware('throttle:10,1');
    Route::post('google', [LmsFeatureController::class, 'google'])->middleware('throttle:10,1');
    Route::post('forgot-password', [PasswordResetController::class, 'requestLink'])->middleware('throttle:5,1');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');
    Route::get('email/verify/{user}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('verification.verify');
});

Route::get('public/courses', [LmsFeatureController::class, 'publicCourses']);
Route::get('public/courses/{id}', [LmsFeatureController::class, 'publicCourse'])->whereNumber('id');
Route::get('public/projects', [MeharaPlatformController::class, 'projects']);
Route::get('public/opportunities', [MeharaPlatformController::class, 'opportunities']);
Route::get('public/mentors', [MeharaPlatformController::class, 'mentors']);
Route::get('public/certificates/{code}', [MeharaPlatformController::class, 'verifyCertificate']);
Route::get('public/news', [MeharaPlatformController::class, 'news']);
Route::get('public/news/{slug}', [MeharaPlatformController::class, 'article']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [ProfileController::class, 'show']);
    Route::get('profile', [ProfileController::class, 'show']);
    Route::patch('profile', [ProfileController::class, 'update']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/email/status', [EmailVerificationController::class, 'status']);
    Route::post('auth/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1');

    Route::middleware('verified')->group(function () {
        Route::get('dashboard', DashboardController::class);
        Route::get('students', StudentDirectoryController::class)->middleware('role:parent,teacher,admin');
        Route::get('students/{student}/progress', StudentProgressController::class);
        Route::apiResource('worksheets', WorksheetController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::get('worksheets/{worksheet}/download', [WorksheetController::class, 'download']);
        Route::apiResource('worksheet-bundles', WorksheetBundleController::class)->only(['index', 'store', 'show']);

        Route::apiResource('assignments', WorksheetAssignmentController::class)
            ->parameters(['assignments' => 'worksheetAssignment'])
            ->only(['index', 'store', 'show']);

        Route::post('assignments/{worksheetAssignment}/submission', [WorksheetSubmissionController::class, 'store']);
        Route::get('submissions/{worksheetSubmission}', [WorksheetSubmissionController::class, 'show']);
        Route::get('submissions/{worksheetSubmission}/download', [WorksheetSubmissionController::class, 'download']);

        Route::get('reviews', [TeacherReviewController::class, 'index'])->middleware('role:teacher,admin');
        Route::post('submissions/{worksheetSubmission}/review', [TeacherReviewController::class, 'store'])->middleware('role:teacher');
        Route::get('reviews/{teacherReview}', [TeacherReviewController::class, 'show']);

        Route::apiResource('performance-reports', PerformanceReportController::class)->only(['index', 'show']);
        Route::get('courses', [LmsFeatureController::class, 'courses']);
        Route::post('courses', [LmsFeatureController::class, 'storeCourse']);
        Route::patch('courses/{id}', [LmsFeatureController::class, 'updateCourse'])->whereNumber('id');
        Route::delete('courses/{id}', [LmsFeatureController::class, 'destroyCourse'])->whereNumber('id');
        Route::post('courses/{id}/lessons', [LmsFeatureController::class, 'storeLesson'])->whereNumber('id');
        Route::get('courses/{id}', [LmsFeatureController::class, 'course'])->whereNumber('id');
        Route::post('courses/{id}/enroll', [LmsFeatureController::class, 'enroll'])->whereNumber('id');
        Route::post('courses/{id}/register', [LmsFeatureController::class, 'registerForCourse'])->whereNumber('id');
        Route::get('course-registrations', [LmsFeatureController::class, 'courseRegistrations']);
        Route::post('course-registrations/{registration}/payment-proof', [LmsFeatureController::class, 'uploadPaymentProof']);
        Route::get('admin/course-registrations', [LmsFeatureController::class, 'adminCourseRegistrations']);
        Route::patch('admin/course-registrations/{registration}', [LmsFeatureController::class, 'reviewCourseRegistration']);
        Route::post('lessons/{id}/complete', [LmsFeatureController::class, 'completeLesson'])->whereNumber('id');
        Route::get('workbooks', [LmsFeatureController::class, 'workbooks']);
        Route::post('workbooks', [LmsFeatureController::class, 'storeWorkbook']);
        Route::get('workbooks/{id}', [LmsFeatureController::class, 'workbook'])->whereNumber('id');
        Route::get('quizzes', [LmsFeatureController::class, 'quizzes']);
        Route::post('quizzes', [LmsFeatureController::class, 'storeQuiz']);
        Route::get('quizzes/{id}', [LmsFeatureController::class, 'quiz'])->whereNumber('id');
        Route::post('quizzes/{id}/attempts', [LmsFeatureController::class, 'submitQuiz'])->whereNumber('id');
        Route::get('content-items', [LmsFeatureController::class, 'contentItems']);
        Route::post('content-items/toggle', [LmsFeatureController::class, 'toggleContentItem']);
        Route::get('recent-activity', [LmsFeatureController::class, 'recent']);
        Route::get('search', [LmsFeatureController::class, 'search'])->middleware('throttle:30,1');
        Route::get('messages', [LmsFeatureController::class, 'messages']);
        Route::post('messages', [LmsFeatureController::class, 'sendMessage']);
        Route::patch('messages/{message}/read', [LmsFeatureController::class, 'readMessage']);
        Route::get('notifications', [LmsFeatureController::class, 'notifications']);
        Route::patch('notifications/{id}/read', [LmsFeatureController::class, 'readNotification']);
        Route::get('calendar-events', [LmsFeatureController::class, 'calendar']);
        Route::post('calendar-events', [LmsFeatureController::class, 'storeCalendarEvent']);
        Route::get('certificates', [LmsFeatureController::class, 'certificates']);
        Route::get('classes', [LmsFeatureController::class, 'classes']);
        Route::post('classes', [LmsFeatureController::class, 'storeClass']);
        Route::post('classes/{class}/students', [LmsFeatureController::class, 'assignStudent']);
        Route::get('subjects', [LmsFeatureController::class, 'subjects']);
        Route::post('subjects', [LmsFeatureController::class, 'storeSubject']);
        Route::get('admin/users', [LmsFeatureController::class, 'users']);
        Route::patch('admin/users/{user}', [LmsFeatureController::class, 'updateUser']);
        Route::get('billing/plans', [LmsFeatureController::class, 'billingPlans']);
        Route::post('billing/interests', [LmsFeatureController::class, 'billingInterest']);
        Route::post('projects', [MeharaPlatformController::class, 'storeProject']);
        Route::post('projects/{id}/apply', [MeharaPlatformController::class, 'applyProject'])->whereNumber('id');
        Route::get('projects/{id}/applications', [MeharaPlatformController::class, 'projectApplications'])->whereNumber('id');
        Route::patch('project-applications/{id}', [MeharaPlatformController::class, 'reviewProjectApplication'])->whereNumber('id');
        Route::post('opportunities', [MeharaPlatformController::class, 'storeOpportunity']);
        Route::post('opportunities/{id}/apply', [MeharaPlatformController::class, 'applyOpportunity'])->whereNumber('id');
        Route::put('mentor-profile', [MeharaPlatformController::class, 'saveMentorProfile']);
        Route::post('mentors/{id}/book', [MeharaPlatformController::class, 'bookMentor'])->whereNumber('id');
        Route::get('mentor-bookings', [MeharaPlatformController::class, 'mentorBookings']);
        Route::get('service-requests', [MeharaPlatformController::class, 'serviceRequests']);
        Route::post('service-requests', [MeharaPlatformController::class, 'storeServiceRequest']);
        Route::patch('service-requests/{id}', [MeharaPlatformController::class, 'updateServiceRequest'])->whereNumber('id');
        Route::get('lessons/{lesson}/note', [MeharaPlatformController::class, 'lessonNote'])->whereNumber('lesson');
        Route::put('lessons/{lesson}/note', [MeharaPlatformController::class, 'saveLessonNote'])->whereNumber('lesson');
        Route::get('courses/{course}/materials', [MeharaPlatformController::class, 'materials'])->whereNumber('course');
        Route::post('courses/{course}/materials', [MeharaPlatformController::class, 'storeMaterial'])->whereNumber('course');
        Route::get('course-materials/{id}/download', [MeharaPlatformController::class, 'downloadMaterial'])->whereNumber('id');
        Route::get('support-tickets', [MeharaPlatformController::class, 'supportTickets']);
        Route::post('support-tickets', [MeharaPlatformController::class, 'storeSupportTicket']);
        Route::get('admin/report-summary', [MeharaPlatformController::class, 'reportSummary']);
        Route::get('learner-overview', [MeharaPlatformController::class, 'learnerOverview']);
        Route::get('admin/subscriptions', [MeharaPlatformController::class, 'subscriptions']);
        Route::patch('admin/subscriptions/{id}', [MeharaPlatformController::class, 'updateSubscription'])->whereNumber('id');
        Route::post('admin/notifications', [MeharaPlatformController::class, 'broadcastNotification']);
        Route::delete('admin/users/{id}', [MeharaPlatformController::class, 'deleteUser'])->whereNumber('id');
        Route::get('admin/news', [MeharaPlatformController::class, 'adminNews']);
        Route::post('admin/news', [MeharaPlatformController::class, 'storeNews']);
        Route::patch('admin/news/{id}', [MeharaPlatformController::class, 'updateNews'])->whereNumber('id');
        Route::delete('admin/news/{id}', [MeharaPlatformController::class, 'destroyNews'])->whereNumber('id');
    });
});
