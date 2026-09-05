<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['sometimes', Rule::in(['teacher', 'student'])],
            'grade_level' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:parent_profiles,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:teacher_profiles,id'],
        ]);

        $data['role'] = $data['role'] ?? 'student';

        if ($data['role'] !== 'student' && (isset($data['parent_id']) || isset($data['teacher_id']))) {
            throw ValidationException::withMessages([
                'role' => ['Parent ID and Teacher ID can only be used when registering a student.'],
            ]);
        }

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
            ]);

            match ($data['role']) {
                'parent' => ParentProfile::create(['user_id' => $user->id, 'phone' => $data['phone'] ?? null]),
                'teacher' => TeacherProfile::create(['user_id' => $user->id, 'specialization' => $data['specialization'] ?? null]),
                'student' => StudentProfile::create([
                    'user_id' => $user->id,
                    'parent_id' => $data['parent_id'] ?? null,
                    'grade_level' => $data['grade_level'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                ]),
            };

            if ($data['role'] === 'student' && isset($data['teacher_id'])) {
                $user->studentProfile->teachers()->attach($data['teacher_id']);
            }

            return $user;
        });

        event(new Registered($user));

        return response()->json($this->tokenPayload($user), 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['The supplied credentials are invalid.']]);
        }

        if ($user->status === 'suspended') {
            abort(403, 'This account is suspended.');
        }

        return response()->json($this->tokenPayload($user));
    }

    public function demoStatus()
    {
        if (! $this->demoModeEnabled()) {
            return response()->json(['enabled' => false]);
        }

        return response()->json([
            'enabled' => true,
            'accounts' => [
                ['label' => 'Student Demo', 'email' => config('auth.demo.student_email')],
                ['label' => 'Instructor Demo', 'email' => config('auth.demo.instructor_email')],
                ['label' => 'Admin Demo', 'email' => config('auth.demo.admin_email')],
            ],
        ]);
    }

    public function demoLogin(Request $request)
    {
        if (! $this->demoModeEnabled()) {
            abort(404);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $email = Str::lower($data['email']);
        if (! Str::endsWith($email, '.test')) {
            throw ValidationException::withMessages(['email' => ['Demo accounts must use the reserved .test domain.']]);
        }

        $configuredPassword = (string) config('auth.demo.password');
        if ($configuredPassword === '') {
            abort(503, 'Demo authentication is not fully configured.');
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            if (! $user->is_test_user || ! Hash::check($data['password'], $user->password)) {
                throw ValidationException::withMessages(['email' => ['The supplied demo credentials are invalid.']]);
            }
        } else {
            if (! hash_equals($configuredPassword, $data['password'])) {
                throw ValidationException::withMessages(['email' => ['The supplied demo credentials are invalid.']]);
            }

            $role = match ($email) {
                Str::lower((string) config('auth.demo.admin_email')) => 'admin',
                Str::lower((string) config('auth.demo.instructor_email')) => 'teacher',
                default => 'student',
            };

            $user = DB::transaction(function () use ($email, $data, $role) {
                $user = User::create([
                    'name' => Str::headline(Str::before($email, '@')),
                    'email' => $email,
                    'password' => $data['password'],
                    'role' => $role,
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'is_test_user' => true,
                ]);

                if ($role === 'teacher') {
                    TeacherProfile::create(['user_id' => $user->id, 'specialization' => 'Demo instructor']);
                } elseif ($role === 'student') {
                    StudentProfile::create(['user_id' => $user->id]);
                }

                return $user;
            });
        }

        if ($user->status === 'suspended') {
            abort(403, 'This account is suspended.');
        }

        return response()->json($this->tokenPayload($user));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->noContent();
    }

    private function tokenPayload(User $user): array
    {
        return [
            'token' => $user->createToken('lms-web')->plainTextToken,
            'portal_path' => $user->portalPath(),
            'user' => $user->load(
                'roles.permissions',
                'parentProfile',
                'teacherProfile',
                'studentProfile.parent.user',
                'studentProfile.teachers.user',
                'studentProfile.schoolClass'
            ),
        ];
    }

    private function demoModeEnabled(): bool
    {
        return app()->environment(['local', 'testing'])
            && ! app()->environment('production')
            && (bool) config('auth.demo.enabled');
    }
}
