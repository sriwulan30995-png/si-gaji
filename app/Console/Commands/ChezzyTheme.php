<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ChezzyTheme extends Command
{
    protected $signature = 'chezzy:theme {theme? : default|neobrutalism|brisk}';

    protected $description = 'Switch Chezzy Filament theme';

    public function handle(): void
    {
        $theme = $this->argument('theme');

        if (!$theme) {
            $theme = $this->chooseTheme();
        }

        if (!in_array($theme, ['default', 'neobrutalism', 'brisk', 'nord', 'awin'])) {
            $this->error('Theme tidak valid');
            return;
        }

        $this->updateConfig($theme);
        $this->updateThemeCss($theme);

        $this->info(" Theme berhasil diubah ke: {$theme}");
        $this->line(' Jalankan: npm run build');
    }

    private function chooseTheme(): string
    {
        $this->line('');
        $this->line(' Pilih tema LaravelChezzy:');
        $this->line(' [1] Default (Laravel Chezzy)');
        $this->line(' [2] Neobrutalism');
        $this->line(' [3] Brisk');
        $this->line(' [4] Nord');
        $this->line(' [5] Awin');
        $this->line('');

        $choice = $this->ask('Masukkan nomor tema [1-5]', '1');

        return match ($choice) {
            '1' => 'default',
            '2' => 'neobrutalism',
            '3' => 'brisk',
            '4' => 'nord',
            '5' => 'awin',
            default => 'default',
        };
    }


    private function updateConfig(string $theme): void
    {
        $path = config_path('chezzy.php');
        $config = file_get_contents($path);

        $config = preg_replace(
            "/'active_theme'\s*=>\s*'.*?'/",
            "'active_theme' => '{$theme}'",
            $config
        );

        file_put_contents($path, $config);
    }

    private function updateThemeCss(string $theme): void
    {
        $path = resource_path('css/filament/admin/theme.css');

        $css = match ($theme) {
            'brisk' => <<<CSS
@import "../../../../vendor/filafly/brisk/resources/css/theme.css";

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/chezzy/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../vendor/devonab/filament-easy-footer/resources/views/**/*';
CSS,

            'neobrutalism' => <<<CSS
@import "../../../../vendor/filament/filament/resources/css/theme.css";

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/chezzy/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../vendor/devonab/filament-easy-footer/resources/views/**/*';
CSS,

            'nord' => <<<CSS
@import "../../../../vendor/filament/filament/resources/css/theme.css";

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/chezzy/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../vendor/devonab/filament-easy-footer/resources/views/**/*';
CSS,

            'awin' => <<<CSS
@import "../../../../vendor/filament/filament/resources/css/theme.css";

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/chezzy/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../vendor/devonab/filament-easy-footer/resources/views/**/*';
CSS,

            default => <<<CSS
@import "../../../../vendor/filament/filament/resources/css/theme.css";

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/chezzy/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../vendor/devonab/filament-easy-footer/resources/views/**/*';
CSS,
        };

        file_put_contents($path, $css);
    }
}
