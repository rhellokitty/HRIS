<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmModalTest extends TestCase
{
    protected User $admin;
    protected User $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@hris.local'],
            ['name' => 'Admin Modal Test', 'role' => User::ROLE_ADMIN, 'password' => 'password']
        );

        $this->karyawan = User::firstOrCreate(
            ['email' => 'karyawan_modal_test@hris.local'],
            ['name' => 'Karyawan Modal Test', 'role' => User::ROLE_KARYAWAN, 'password' => 'password']
        );
    }

    /**
     * Pastikan tidak ada satupun onclick confirm atau alert di seluruh blade template.
     */
    public function test_no_native_confirm_or_alert_in_views(): void
    {
        $viewsPath = resource_path('views');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewsPath));

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getRealPath());

                $this->assertDoesNotMatchRegularExpression(
                    '/onclick\s*=\s*["\']return\s+confirm\(/i',
                    $content,
                    "Ditemukan native confirm pada file: {$file->getFilename()}"
                );

                $this->assertDoesNotMatchRegularExpression(
                    '/(window\.)?alert\s*\(/i',
                    $content,
                    "Ditemukan native alert pada file: {$file->getFilename()}"
                );
            }
        }
    }

    /**
     * Pastikan partial custom confirm modal ter-render di Dashboard Admin.
     */
    public function test_confirm_modal_rendered_in_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('id="hris-confirm-modal"', false);
        $response->assertSee('id="hris-confirm-dialog"', false);
        $response->assertSee('data-confirm-variant="primary"', false);
    }

    /**
     * Pastikan partial custom confirm modal ter-render di Master Shift Admin.
     */
    public function test_confirm_modal_rendered_in_admin_shifts(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.shifts.index'));
        $response->assertStatus(200);
        $response->assertSee('id="hris-confirm-modal"', false);
        $response->assertSee('data-confirm-title="Hapus Master Shift"', false);
    }

    /**
     * Pastikan partial custom confirm modal ter-render di Jadwal Admin.
     */
    public function test_confirm_modal_rendered_in_admin_schedules(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.schedules.index'));
        $response->assertStatus(200);
        $response->assertSee('id="hris-confirm-modal"', false);
        $response->assertSee('data-confirm-title="Generate Jadwal Mingguan"', false);
    }

    /**
     * Pastikan partial custom confirm modal ter-render di Beranda Karyawan.
     */
    public function test_confirm_modal_rendered_in_employee_home(): void
    {
        $response = $this->actingAs($this->karyawan)->get(route('employee.home'));
        $response->assertStatus(200);
        $response->assertSee('id="hris-confirm-modal"', false);
    }
}
