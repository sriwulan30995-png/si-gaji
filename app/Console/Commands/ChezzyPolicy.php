<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ChezzyPolicy extends Command
{
    protected $signature = 'chezzy:policy';
    protected $description = 'Generate policy files for selected models';

    public function handle()
    {
        $modelsDir = app_path('Models');
        $models = collect(File::files($modelsDir))
            ->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))
            ->values()
            ->all();

        if (empty($models)) {
            return $this->error("Tidak ada model ditemukan di direktori $modelsDir");
        }

        $this->info("\nModel Tersedia:");
        foreach ($models as $i => $model) {
            $this->line(" [\033[33m$i\033[0m] $model");
        }

        $indexes = $this->ask("\nMasukkan nomor model yang ingin dibuatkan policy (pisahkan dengan koma, contoh: 0,1,3)");

        $selectedIndexes = array_map('trim', explode(',', $indexes));
        $selectedModels = collect($selectedIndexes)
            ->filter(fn($i) => isset($models[$i]))
            ->map(fn($i) => $models[$i])
            ->all();

        if (empty($selectedModels)) {
            return $this->error("Tidak ada model valid yang dipilih.");
        }

        foreach ($selectedModels as $model) {
            $this->createPolicy($model);
        }
    }

    private function createPolicy(string $model)
    {
        $policyName = "{$model}Policy";

        $this->info("\n📄 Membuat policy untuk model: $model...");

        // Jalankan make:policy
        $process = Process::fromShellCommandline("php artisan make:policy {$policyName} --model={$model}");
        $process->run();

        if (!$process->isSuccessful()) {
            return $this->error("Gagal membuat policy untuk model $model.");
        }

        $this->info("✅ Policy $policyName berhasil dibuat.");

        // Overwrite dengan isi custom
        $policyPath = app_path("Policies/{$policyName}.php");

        if (!File::exists($policyPath)) {
            return $this->error("Policy file tidak ditemukan: $policyPath");
        }

        $this->overwritePolicyContent($model, $policyPath);
    }

    private function overwritePolicyContent(string $model, string $path)
    {
        $template = $this->generatePolicyContent($model);
        File::put($path, $template);
        $this->info("✍️  Policy $model berhasil diisi dengan template permission.");
    }

    private function generatePolicyContent(string $model): string
    {
        $modelClass = "App\\Models\\$model";
        $policyName = "{$model}Policy";
        $lower = Str::title(Str::snake($model, ' '));

        return <<<PHP
<?php

namespace App\Policies;

use App\Models\User;
use $modelClass;
use Illuminate\Auth\Access\Response;

class $policyName
{
    public function viewAny(User \$user): bool
    {
        return \$user->can('View Any $lower');
    }

    public function view(User \$user, $model \$model): bool
    {
        return \$user->can('View $lower');
    }

    public function create(User \$user): bool
    {
        return \$user->can('Create $lower');
    }

    public function update(User \$user, $model \$model): bool
    {
        return \$user->can('Update $lower');
    }

    public function delete(User \$user, $model \$model): bool
    {
        return \$user->can('Delete $lower');
    }

    public function restore(User \$user, $model \$model): bool
    {
        return \$user->can('Restore $lower');
    }

    public function forceDelete(User \$user, $model \$model): bool
    {
        return \$user->can('Force Delete $lower');
    }
}
PHP;
    }
}
