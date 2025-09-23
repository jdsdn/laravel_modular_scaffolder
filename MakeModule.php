<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeModule extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = 'Scaffold a new module with model, controllers, validations, services, tests, routes, model, and seeders folder';

    protected Filesystem $files;

    public function __construct()
    {
        parent::__construct();
        $this->files = new Filesystem();
    }

    public function handle()
    {
        $modulesRoot = app_path('Modules');
        
        if (! $this->files->exists($modulesRoot)) {
            $this->files->makeDirectory($modulesRoot, 0755, true);
            $this->info('Created Modules folder.');
        }

        $moduleName = $this->argument('name');
        $modulePath = app_path("Modules/{$moduleName}");

        if ($this->files->exists($modulePath)) {
            $this->error("Module {$moduleName} already exists!");
            return 1;
        }

        $this->info("Creating module: {$moduleName}");

        // 1. Create folders
        $folders = [
            $modulePath,
            "{$modulePath}/Controllers",
            "{$modulePath}/Services",
            "{$modulePath}/Validations",
            "{$modulePath}/Tests/Feature",
            "{$modulePath}/Tests/Unit",
            "{$modulePath}/Seeders",
        ];

        foreach ($folders as $folder) {
            $this->files->makeDirectory($folder, 0755, true);
        }

        // 2. Create model stub
        $modelStub = "<?php

namespace App\Modules\\{$moduleName};

use Illuminate\Database\Eloquent\Model;

class {$moduleName} extends Model
{
    protected \$fillable = [];
}
";
        $this->files->put("{$modulePath}/{$moduleName}.php", $modelStub);

        $migrationName = "create_".strtolower($moduleName)."_table";
        $this->call('make:migration', [
            'name' => $migrationName,
            '--create' => strtolower($moduleName),
        ]);

        $this->info("Migration {$migrationName} created in database/migrations");

        // 3. Create controller stub
        $controllerStub = "<?php

namespace App\Modules\\{$moduleName}\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\\{$moduleName}\Validations\Store{$moduleName}Request;
use App\Modules\\{$moduleName}\Validations\Show{$moduleName}Request;
use App\Modules\\{$moduleName}\Validations\Update{$moduleName}Request;
use App\Modules\\{$moduleName}\Validations\Delete{$moduleName}Request;

class {$moduleName}Controller extends Controller
{
    public function index()
    {
        // TODO: return list
    }

    public function store(Store{$moduleName}Request \$request)
    {
        // TODO: create
    }

    public function show(Show{$moduleName}Request \$request, \$id)
    {
        // TODO: show
    }

    public function update(Update{$moduleName}Request \$request, \$id)
    {
        // TODO: update
    }

    public function destroy(Delete{$moduleName}Request \$request, \$id)
    {
        // TODO: delete
    }
}
";
        $this->files->put("{$modulePath}/Controllers/{$moduleName}Controller.php", $controllerStub);

        // 4. Create validation stubs
        $validationTypes = ['Store', 'Show', 'Update', 'Delete'];
        foreach ($validationTypes as $type) {
            $rules = ($type === 'Store') ? [] : ["'id' => 'required'"];
            $rulesString = empty($rules) ? '' : implode(",\n            ", $rules);
            $validationStub = "<?php

namespace App\Modules\\{$moduleName}\Validations;

use Illuminate\Foundation\Http\FormRequest;

class {$type}{$moduleName}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            {$rulesString}
        ];
    }
}
";
            $this->files->put("{$modulePath}/Validations/{$type}{$moduleName}Request.php", $validationStub);
        }

        // 5. Create service stub
        $serviceStub = "<?php

namespace App\Modules\\{$moduleName}\Services;

class {$moduleName}Service
{
    // TODO: implement business logic
}
";
        $this->files->put("{$modulePath}/Services/{$moduleName}Service.php", $serviceStub);
        $lowerModuleName = strtolower($moduleName);

        // 6. Create routes stub
        $routesStub = "<?php

use Illuminate\Support\Facades\Route;
use App\Modules\\{$moduleName}\Controllers\\{$moduleName}Controller;

Route::apiResource('{$lowerModuleName}', {$moduleName}Controller::class);
";
        $this->files->put("{$modulePath}/routes.php", $routesStub);

        // 7. Create test stubs
        $featureTestStub = "<?php

namespace Tests\Feature\Modules\\{$moduleName};

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class {$moduleName}FeatureTest extends TestCase
{
    #[Test]
    public function example_feature_test()
    {
        \$this->assertTrue(true);
    }
}
";
        $unitTestStub = "<?php

namespace Tests\Unit\Modules\\{$moduleName};

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class {$moduleName}UnitTest extends TestCase
{
    #[Test]
    public function example_unit_test()
    {
        \$this->assertTrue(true);
    }
}
";
        $this->files->put("{$modulePath}/Tests/Feature/{$moduleName}FeatureTest.php", $featureTestStub);
        $this->files->put("{$modulePath}/Tests/Unit/{$moduleName}UnitTest.php", $unitTestStub);

        $this->info("Module {$moduleName} created successfully!");
        return 0;
    }
}
