<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        \Carbon\Carbon::setLocale('id');

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function (\Illuminate\Auth\Events\Login $event) {
            \App\Models\ActivityLog::record(
                'Login',
                'User ' . $event->user->name . ' (' . $event->user->role . ') berhasil masuk ke sistem.',
                $event->user->id
            );
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function (\Illuminate\Auth\Events\Logout $event) {
            if ($event->user) {
                \App\Models\ActivityLog::record(
                    'Logout',
                    'User ' . $event->user->name . ' keluar dari sistem.',
                    $event->user->id
                );
            }
        });

        \Illuminate\Support\Facades\View::composer(['partials.topbar', 'layouts.dashboard'], function ($view) {
            $notifications = [];

            try {
                $authUser = auth()->user();
                $isPetugasUser = $authUser && $authUser->role === 'petugas';

                if (\Illuminate\Support\Facades\Schema::hasTable('area_parkirs')) {
                    $areas = \App\Models\AreaParkir::all();
                    foreach ($areas as $area) {
                        if ($area->status === 'aktif') {
                            $targetAreaUrl = $isPetugasUser ? route('petugas.transaksi.index', ['area_id' => $area->id]) : route('admin.area.index');

                            if ($area->slot_tersedia <= 0) {
                                $notifications[] = [
                                    'type' => 'danger',
                                    'title' => 'Area Parkir Penuh!',
                                    'message' => "Area {$area->nama_area} telah mencapai kapasitas maksimal ({$area->kapasitas_total} slot).",
                                    'url' => $targetAreaUrl,
                                    'icon' => 'alert-circle',
                                ];
                            } elseif ($area->persentase_terisi >= 85) {
                                $notifications[] = [
                                    'type' => 'warning',
                                    'title' => 'Area Parkir Hampir Penuh',
                                    'message' => "Area {$area->nama_area} terisi {$area->slot_terisi}/{$area->kapasitas_total} slot ({$area->persentase_terisi}%).",
                                    'url' => $targetAreaUrl,
                                    'icon' => 'alert-triangle',
                                ];
                            }
                        }
                    }
                }

                if (\Illuminate\Support\Facades\Schema::hasTable('transaksis')) {
                    $overstay = \App\Models\Transaksi::where('status', 'parkir')
                        ->where('waktu_masuk', '<=', now()->subHours(24))
                        ->take(3)
                        ->get();
                    foreach ($overstay as $tr) {
                        $targetOverstayUrl = $isPetugasUser
                            ? route('petugas.transaksi.index', ['search' => $tr->plat_nomor])
                            : route('admin.kendaraan.index', ['search' => $tr->plat_nomor]);

                        $notifications[] = [
                            'type' => 'info',
                            'title' => 'Parkir > 24 Jam',
                            'message' => "Kendaraan {$tr->plat_nomor} terparkir sejak {$tr->waktu_masuk->diffForHumans()}.",
                            'url' => $targetOverstayUrl,
                            'icon' => 'clock',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Ignore during migrations/setup
            }

            $view->with('systemNotifications', $notifications);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
