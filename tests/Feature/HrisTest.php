<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HrisTest extends TestCase
{
    protected User $admin;
    protected User $operator;
    protected User $karyawan;
    protected Shift $shiftPagi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@hris.local'],
            ['name' => 'Admin Test', 'role' => User::ROLE_ADMIN, 'password' => 'password']
        );

        $this->operator = User::firstOrCreate(
            ['email' => 'operator_test@hris.local'],
            ['name' => 'Operator Test', 'role' => User::ROLE_OPERATOR, 'password' => 'password']
        );

        $this->karyawan = User::firstOrCreate(
            ['email' => 'karyawan_test@hris.local'],
            ['name' => 'Karyawan Test', 'nip' => '99990001', 'role' => User::ROLE_KARYAWAN, 'password' => 'password']
        );

        $this->shiftPagi = Shift::firstOrCreate(
            ['name' => 'Shift Pagi Test'],
            ['start_time' => '08:00', 'end_time' => '16:00']
        );

        // Bersihkan data sementara test user agar tes bersifat idempoten
        Attendance::where('user_id', $this->karyawan->id)->delete();
        LeaveRequest::where('user_id', $this->karyawan->id)->delete();
    }

    /**
     * Test 1a: Autentikasi Karyawan Mengarahkan ke Beranda Karyawan
     */
    public function test_karyawan_login_redirects_to_employee_home(): void
    {
        $response = $this->post(route('login.attempt'), [
            'login' => $this->karyawan->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('employee.home'));
    }

    /**
     * Test 1b: Autentikasi Admin Mengarahkan ke Dashboard Admin
     */
    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $response = $this->post(route('login.attempt'), [
            'login' => $this->admin->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test 2: Hak Akses Role Middleware (Karyawan dilarang akses menu Admin)
     */
    public function test_employee_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->karyawan)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /**
     * Test 3: Modul Absensi (Anti-Manipulasi, GPS Geolocation & Server-Side Time Locking)
     */
    public function test_employee_can_clock_in_with_geolocation_and_server_time_is_locked(): void
    {
        $today = now()->toDateString();

        // Buat jadwal untuk karyawan hari ini
        $schedule = Schedule::firstOrCreate(
            ['user_id' => $this->karyawan->id, 'date' => $today],
            ['shift_id' => $this->shiftPagi->id]
        );

        // Hapus absensi sebelumnya jika ada
        Attendance::where('schedule_id', $schedule->id)->delete();

        // Clock-In mengirim titik koordinat GPS
        $response = $this->actingAs($this->karyawan)->post(route('employee.attendance.clock-in'), [
            'latitude' => -6.2087634,
            'longitude' => 106.8455990,
        ]);

        $response->assertRedirect(route('employee.attendance.index'));

        // Verifikasi record di database
        $attendance = Attendance::where('user_id', $this->karyawan->id)
            ->where('schedule_id', $schedule->id)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->clock_in_time);
        $this->assertEquals('-6.2087634', $attendance->lat_in);
        $this->assertEquals('106.8455990', $attendance->lng_in);
        $this->assertNull($attendance->clock_out_time);

        // Clock-Out
        $responseOut = $this->actingAs($this->karyawan)->post(route('employee.attendance.clock-out'), [
            'latitude' => -6.2087640,
            'longitude' => 106.8456010,
        ]);

        $responseOut->assertRedirect(route('employee.attendance.index'));

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out_time);
        $this->assertEquals('-6.2087640', $attendance->lat_out);
        $this->assertEquals('106.8456010', $attendance->lng_out);
    }

    /**
     * Test 4: Pencegahan Double Clock-In
     */
    public function test_employee_cannot_double_clock_in(): void
    {
        $today = now()->toDateString();
        $schedule = Schedule::firstOrCreate(
            ['user_id' => $this->karyawan->id, 'date' => $today],
            ['shift_id' => $this->shiftPagi->id]
        );

        Attendance::where('user_id', $this->karyawan->id)->delete();

        // Clock in 1
        $this->actingAs($this->karyawan)->post(route('employee.attendance.clock-in'), [
            'latitude' => -6.2,
            'longitude' => 106.8,
        ]);

        // Clock in 2 (harus ditolak karena sesi masih aktif)
        $response = $this->actingAs($this->karyawan)->post(route('employee.attendance.clock-in'), [
            'latitude' => -6.2,
            'longitude' => 106.8,
        ]);

        $response->assertSessionHas('error');
    }

    /**
     * Test 5: Auto-Generate Jadwal Mingguan oleh Admin (PRD §3.B)
     */
    public function test_admin_can_generate_weekly_schedules(): void
    {
        $nextWeekStart = now()->addWeeks(2)->startOfWeek();

        $response = $this->actingAs($this->admin)->post(route('admin.schedules.generate'), [
            'start_date' => $nextWeekStart->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Pastikan jadwal terbuat di database
        $schedulesCount = Schedule::whereDate('date', '>=', $nextWeekStart->toDateString())
            ->whereDate('date', '<=', $nextWeekStart->copy()->endOfWeek()->toDateString())
            ->count();

        $this->assertGreaterThan(0, $schedulesCount);
    }

    /**
     * Test 6: Persetujuan Cuti / Izin oleh Admin (PRD §3.C)
     */
    public function test_admin_can_approve_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'user_id' => $this->karyawan->id,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(11)->toDateString(),
            'type' => LeaveRequest::TYPE_CUTI,
            'reason' => 'Pengujian cuti otomatis',
            'status' => LeaveRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.leaves.approve', $leave));
        $response->assertRedirect();

        $leave->refresh();
        $this->assertEquals(LeaveRequest::STATUS_APPROVED, $leave->status);
    }

    /**
     * Test 7: Master Shift CRUD
     */
    public function test_admin_can_create_new_shift(): void
    {
        $shiftName = 'Shift Khusus '.uniqid();

        $response = $this->actingAs($this->admin)->post(route('admin.shifts.store'), [
            'name' => $shiftName,
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);

        $response->assertRedirect(route('admin.shifts.index'));
        $this->assertDatabaseHas('shifts', ['name' => $shiftName]);
    }

    /**
     * Test 8: Operator Role Access (Read-Only ke Dashboard Monitoring, dilarang Master Shift)
     */
    public function test_operator_can_access_dashboard_but_cannot_create_shift(): void
    {
        // Operator boleh buka dashboard monitoring
        $response = $this->actingAs($this->operator)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Operator dilarang menambah shift baru
        $responseCreate = $this->actingAs($this->operator)->post(route('admin.shifts.store'), [
            'name' => 'Shift Ilegal',
            'start_time' => '08:00',
            'end_time' => '16:00',
        ]);
        $responseCreate->assertStatus(403);
    }

    /**
     * Test 9: Karyawan Mengajukan Cuti / Izin (PRD §3.C Form Karyawan)
     */
    public function test_employee_can_submit_leave_request(): void
    {
        $response = $this->actingAs($this->karyawan)->post(route('employee.leave.store'), [
            'start_date' => now()->addDays(20)->toDateString(),
            'end_date' => now()->addDays(22)->toDateString(),
            'type' => LeaveRequest::TYPE_IZIN,
            'reason' => 'Urusan administrasi keluarga penting.',
        ]);

        $response->assertRedirect(route('employee.leave.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->karyawan->id,
            'type' => LeaveRequest::TYPE_IZIN,
            'status' => LeaveRequest::STATUS_PENDING,
        ]);
    }

    /**
     * Test 10: Validasi Geolocation (Wajib mengirimkan koordinat)
     */
    public function test_clock_in_requires_latitude_and_longitude(): void
    {
        $response = $this->actingAs($this->karyawan)->post(route('employee.attendance.clock-in'), []);
        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }
}
