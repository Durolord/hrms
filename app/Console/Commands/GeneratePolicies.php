<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class GeneratePolicies extends Command
{
    protected $signature = 'shield:generate-policies 
                            {--models= : Comma-separated model names} 
                            {--all : Generate for all models}
                            {--report : Show permissions report}';

    protected $description = 'Generate Filament Shield policies with special permissions';

    protected $specialPermissions = [
        'Employee' => ['terminate', 'promote', 'transfer', 'manageDocuments'],
        'Leave' => ['approve', 'reject', 'override', 'retroactive', 'bulkApprove'],
        'Payroll' => ['recalculate', 'generatePayslip'],
        'PayScale' => ['activate', 'deactivate', 'link_designations'],
    ];

    public function handle()
    {
        $models = $this->option('all')
            ? $this->getAllModels()
            : explode(',', $this->option('models'));
        $reportData = [];
        foreach ($models as $model) {
            $modelName = class_basename($model);
            $specialPermissions = $this->getSpecialPermissionsForModel($modelName);
            $this->generatePolicy($modelName, $model, $specialPermissions);
            if (! empty($specialPermissions)) {
                $reportData[$modelName] = $specialPermissions;
            }
        }
        if ($this->option('report')) {
            $this->displayPermissionsReport($reportData);
        }
        $this->info('Policies generation completed!');
    }

    protected function getSpecialPermissionsForModel(string $modelName): array
    {
        return $this->specialPermissions[$modelName] ?? [];
    }

    protected function generatePolicy(string $modelName, string $modelClass, array $specialPermissions)
    {
        $policyClass = "{$modelName}Policy";
        $policyPath = app_path("Policies/{$policyClass}.php");
        if (File::exists($policyPath)) {
            $this->warn("Policy for {$modelName} already exists. Skipping...");

            return;
        }
        $stub = File::get(__DIR__.'/stubs/policy.stub');
        $replacements = $this->prepareReplacements($modelName, $modelClass, $specialPermissions);
        $policyContent = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );
        File::ensureDirectoryExists(app_path('Policies'));
        File::put($policyPath, $policyContent);
        $this->info("Policy for {$modelName} created with ".count($specialPermissions).' special permissions');
    }

    protected function prepareReplacements(string $modelName, string $modelClass, array $specialPermissions): array
    {
        $resourceName = Str::plural(Str::kebab($modelName));
        $modelVariable = Str::camel($modelName);
        $baseReplacements = [
            '{{ namespace }}' => 'App\Policies',
            '{{ model }}' => $modelName,
            '{{ modelClass }}' => $modelClass,
            '{{ modelVariable }}' => $modelVariable,
            '{{ resourceName }}' => $resourceName,
            '{{ standardMethods }}' => $this->generateStandardMethods($resourceName),
            '{{ specialMethods }}' => $this->generateSpecialMethods($resourceName, $specialPermissions),
        ];

        return $baseReplacements;
    }

    protected function generateStandardMethods(string $resourceName): string
    {
        return implode("\n\n", [
            $this->buildMethod('viewAny', $resourceName),
            $this->buildMethod('view', $resourceName),
            $this->buildMethod('create', $resourceName),
            $this->buildMethod('update', $resourceName),
            $this->buildMethod('delete', $resourceName),
            $this->buildMethod('restore', $resourceName),
            $this->buildMethod('export', $resourceName),
            $this->buildMethod('import', $resourceName),
        ]);
    }

    protected function generateSpecialMethods(string $resourceName, array $permissions): string
    {
        return implode("\n\n", array_map(
            fn ($permission) => $this->buildMethod($permission, $resourceName),
            $permissions
        ));
    }

    protected function buildMethod(string $permission, string $resourceName): string
    {
        $methodName = Str::camel($permission);
        $permission = Str::snake($permission);
        $resourceName = Str::snake($resourceName);
        $resourceName = Str::replaceFirst('-', '::', $resourceName);
        $permissionString = "{$permission}_{$resourceName}";

        return <<<METHOD
    public function {$methodName}(User \$user)
    {
        return \$user->can('{$permissionString}');
    }
METHOD;
    }

    protected function displayPermissionsReport(array $reportData): void
    {
        $this->info("\nSpecial Permissions Report:");
        $this->table(
            ['Model', 'Special Permissions', 'Permission Strings'],
            collect($reportData)->map(function ($permissions, $model) {
                $resource = Str::plural(Str::kebab($model));

                return [
                    $model,
                    implode(', ', $permissions),
                    implode("\n", array_map(fn ($p) => "$p $resource", $permissions)),
                ];
            })
        );
    }

    protected function getAllModels()
    {
        $modelPath = app_path('Models');

        return collect(File::allFiles($modelPath))
            ->map(function ($file) {
                return 'App\\Models\\'.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    $file->getRelativePathname()
                );
            })
            ->filter(function ($class) {
                return class_exists($class) && (new ReflectionClass($class))->isInstantiable();
            })
            ->toArray();
    }
}
