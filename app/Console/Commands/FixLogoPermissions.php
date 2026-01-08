<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixLogoPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logos:fix-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige los permisos de todos los archivos de logos en storage/app/public/logos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Corrigiendo permisos de logos...');

        $logosDir = Storage::disk('public')->path('logos');

        if (!is_dir($logosDir)) {
            $this->error("El directorio logos no existe: {$logosDir}");
            return 1;
        }

        // Corregir permisos del directorio
        if (is_dir($logosDir)) {
            @chmod($logosDir, 0775);
            $this->info("✅ Permisos del directorio corregidos: {$logosDir}");
        }

        // Obtener todos los archivos en el directorio logos
        $files = Storage::disk('public')->files('logos');

        if (empty($files)) {
            $this->warn('No se encontraron archivos de logos');
            return 0;
        }

        $fixed = 0;
        $failed = 0;

        foreach ($files as $file) {
            $fullPath = Storage::disk('public')->path($file);

            if (!file_exists($fullPath)) {
                continue;
            }

            // Establecer permisos: 0664 (rw-rw-r--)
            if (@chmod($fullPath, 0664)) {
                $fixed++;
                $this->line("  ✅ {$file}");
            } else {
                $failed++;
                $this->error("  ❌ No se pudo cambiar permisos: {$file}");
            }

            // Intentar cambiar propietario si tenemos permisos (solo root)
            if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
                @chown($fullPath, 'www-data');
                if (function_exists('posix_getgrnam')) {
                    $groupInfo = @posix_getgrnam('www-data');
                    if ($groupInfo) {
                        @chgrp($fullPath, 'www-data');
                    }
                }
            }
        }

        $this->info("\n📊 Resumen:");
        $this->info("   ✅ Archivos corregidos: {$fixed}");
        if ($failed > 0) {
            $this->warn("   ❌ Archivos con error: {$failed}");
        }

        $this->info("\n💡 Si los permisos aún no funcionan, ejecuta manualmente:");
        $this->line("   sudo chown -R www-data:www-data {$logosDir}");
        $this->line("   sudo chmod -R 664 {$logosDir}");
        $this->line("   sudo find {$logosDir} -type d -exec chmod 775 {} \\;");

        return 0;
    }
}

