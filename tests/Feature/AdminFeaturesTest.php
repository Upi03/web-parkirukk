<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AreaParkir;
use App\Models\Setting;
use App\Models\Tarif;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeaturesTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'admin')->first();
        if (!$this->admin) {
            $this->admin = User::create([
                'name' => 'Admin Test',
                'email' => 'admintest@smarpark.local',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }
    }

    public function test_admin_can_access_all_pages()
    {
        $this->actingAs($this->admin);

        $routes = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.tarif.index'),
            route('admin.area.index'),
            route('admin.kendaraan.index'),
            route('admin.log.index'),
            route('admin.profil.index'),
            route('admin.pengaturan.index'),
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_user_management_crud_and_self_delete_protection()
    {
        $this->actingAs($this->admin);

        // 1. Create User
        $response = $this->post(route('admin.users.store'), [
            'name' => 'User Baru Test',
            'email' => 'userbaru@test.com',
            'password' => 'secret123',
            'role' => 'petugas',
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'userbaru@test.com']);

        $createdUser = User::where('email', 'userbaru@test.com')->first();

        // 2. Update User
        $response = $this->put(route('admin.users.update', $createdUser->id), [
            'name' => 'User Baru Diedit',
            'email' => 'userbaru@test.com',
            'role' => 'petugas',
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['name' => 'User Baru Diedit']);

        // 3. Toggle Status
        $response = $this->patch(route('admin.users.toggle-status', $createdUser->id));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertFalse((bool) $createdUser->fresh()->is_active);

        // 4. Admin cannot delete self
        $response = $this->delete(route('admin.users.destroy', $this->admin->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        // 5. Delete created user
        $response = $this->delete(route('admin.users.destroy', $createdUser->id));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $createdUser->id]);
    }

    public function test_tarif_crud()
    {
        $this->actingAs($this->admin);

        // Create Tarif
        $response = $this->post(route('admin.tarif.store'), [
            'jenis_kendaraan' => 'Sepeda Listrik',
            'tarif_per_jam' => 1500,
            'tarif_pertama' => 2000,
            'tarif_maksimal' => 10000,
        ]);
        $response->assertRedirect(route('admin.tarif.index'));
        $this->assertDatabaseHas('tarifs', ['jenis_kendaraan' => 'Sepeda Listrik']);

        $tarif = Tarif::where('jenis_kendaraan', 'Sepeda Listrik')->first();

        // Update Tarif
        $response = $this->put(route('admin.tarif.update', $tarif->id), [
            'jenis_kendaraan' => 'Sepeda Listrik',
            'tarif_per_jam' => 1800,
            'tarif_pertama' => 2000,
            'tarif_maksimal' => 12000,
        ]);
        $response->assertRedirect(route('admin.tarif.index'));
        $this->assertDatabaseHas('tarifs', ['tarif_per_jam' => 1800]);

        // Delete Tarif
        $response = $this->delete(route('admin.tarif.destroy', $tarif->id));
        $response->assertRedirect(route('admin.tarif.index'));
        $this->assertDatabaseMissing('tarifs', ['id' => $tarif->id]);
    }

    public function test_area_parkir_crud_and_capacity_validation()
    {
        $this->actingAs($this->admin);

        // Create Area
        $response = $this->post(route('admin.area.store'), [
            'nama_area' => 'Rooftop Zona C',
            'kapasitas_total' => 20,
            'keterangan' => 'Area terbuka lantai teratas',
            'status' => 'aktif',
        ]);
        $response->assertRedirect(route('admin.area.index'));
        $this->assertDatabaseHas('area_parkirs', ['nama_area' => 'Rooftop Zona C']);

        $area = AreaParkir::where('nama_area', 'Rooftop Zona C')->first();

        // Update Area
        $response = $this->put(route('admin.area.update', $area->id), [
            'nama_area' => 'Rooftop Zona C',
            'kapasitas_total' => 25,
            'keterangan' => 'Area lantai atap berkanopi',
            'status' => 'aktif',
        ]);
        $response->assertRedirect(route('admin.area.index'));
        $this->assertDatabaseHas('area_parkirs', ['kapasitas_total' => 25]);

        // Delete Area
        $response = $this->delete(route('admin.area.destroy', $area->id));
        $response->assertRedirect(route('admin.area.index'));
        $this->assertDatabaseMissing('area_parkirs', ['id' => $area->id]);
    }

    public function test_kendaraan_monitoring_and_management()
    {
        $this->actingAs($this->admin);

        $transaksi = Transaksi::first();
        $tarif = Tarif::first();
        $area = AreaParkir::where('status', 'aktif')->first();

        // 1. Admin can access monitoring with filters
        $response = $this->get(route('admin.kendaraan.index', [
            'status' => 'parkir',
            'search' => $transaksi->plat_nomor,
        ]));
        $response->assertStatus(200);

        // 2. Admin can edit/correct a vehicle record
        $response = $this->put(route('admin.kendaraan.update', $transaksi->id), [
            'plat_nomor' => 'B 9999 KOR',
            'tarif_id' => $tarif->id,
            'area_parkir_id' => $area->id,
            'status' => 'parkir',
        ]);
        $response->assertRedirect(route('admin.kendaraan.index'));
        $this->assertDatabaseHas('transaksis', ['id' => $transaksi->id, 'plat_nomor' => 'B 9999 KOR']);

        // 3. Admin can delete a vehicle record
        $response = $this->delete(route('admin.kendaraan.destroy', $transaksi->id));
        $response->assertRedirect(route('admin.kendaraan.index'));
        $this->assertDatabaseMissing('transaksis', ['id' => $transaksi->id]);
    }
}
