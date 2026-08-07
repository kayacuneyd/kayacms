<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ModuleCreate extends BaseCommand
{
    protected $group       = 'Generators';
    protected $name        = 'module:create';
    protected $description = 'Create a new feature module (vertical slice)';
    protected $usage       = 'module:create [module_name]';

    public function run(array $params)
    {
        $module = $params[0] ?? CLI::prompt('Module name');
        $module = ucfirst($module);
        $path   = ROOTPATH . "modules/{$module}";

        if (is_dir($path)) {
            CLI::error("{$module} module already exists!");
            return;
        }

        // Create directory structure
        $dirs = [
            "{$path}/Config",
            "{$path}/Controllers/Api",
            "{$path}/Controllers/Admin",
            "{$path}/Database/Migrations",
            "{$path}/Database/Seeds",
            "{$path}/Entities",
            "{$path}/Filters",
            "{$path}/Helpers",
            "{$path}/Libraries",
            "{$path}/Models",
            "{$path}/Validation",
            "{$path}/Views",
        ];

        foreach ($dirs as $dir) {
            mkdir($dir, 0755, true);
            CLI::write("Created: {$dir}", 'green');
        }

        // Create Routes.php
        file_put_contents("{$path}/Config/Routes.php", $this->routesTemplate($module));
        CLI::write("Created: {$path}/Config/Routes.php", 'green');

        // Create API Controller
        file_put_contents("{$path}/Controllers/Api/{$module}Controller.php", $this->controllerTemplate($module));
        CLI::write("Created: {$path}/Controllers/Api/{$module}Controller.php", 'green');

        // Create Model
        file_put_contents("{$path}/Models/{$module}Model.php", $this->modelTemplate($module));
        CLI::write("Created: {$path}/Models/{$module}Model.php", 'green');

        CLI::newLine();
        CLI::write("✅ {$module} module created successfully!", 'green');
        CLI::newLine();
        CLI::write("📌 Next steps:", 'yellow');
        CLI::write("1. Add to app/Config/Autoload.php:", 'white');
        CLI::write("   '{$module}' => ROOTPATH . 'modules/{$module}',", 'cyan');
        CLI::write("2. Create migration and model as needed", 'white');
        CLI::write("3. Run: php spark migrate", 'white');
    }

    private function routesTemplate(string $module): string
    {
        $lower = strtolower($module);
        return "<?php\nnamespace {$module}\\Config;\n\n\$routes = service('routes');\n\n\$routes->group('api/{$lower}', ['namespace' => '{$module}\\Controllers\\Api'], static function (\$routes) {\n    \$routes->get('/', '{$module}Controller::index');\n    \$routes->get('(:num)', '{$module}Controller::show/\$1');\n    \$routes->post('/', '{$module}Controller::create', ['filter' => 'apiAuth']);\n    \$routes->put('(:num)', '{$module}Controller::update/\$1', ['filter' => 'apiAuth']);\n    \$routes->delete('(:num)', '{$module}Controller::delete/\$1', ['filter' => 'apiAuth']);\n});\n";
    }

    private function controllerTemplate(string $module): string
    {
        return "<?php\nnamespace {$module}\\Controllers\\Api;\n\nuse App\\Core\\BaseController;\nuse {$module}\\Models\\{$module}Model;\nuse CodeIgniter\\HTTP\\ResponseInterface;\n\nclass {$module}Controller extends BaseController\n{\n    protected \${$module}Model \$model;\n\n    public function __construct()\n    {\n        \$this->model = new {$module}Model();\n    }\n\n    public function index(): ResponseInterface\n    {\n        \$data = \$this->model->findAll();\n        return \$this->respond(['data' => \$data]);\n    }\n\n    public function show(\$id = null): ResponseInterface\n    {\n        \$item = \$this->model->find(\$id);\n        if (!\$item) return \$this->failNotFound('{$module} not found');\n        return \$this->respond(['data' => \$item]);\n    }\n\n    public function create(): ResponseInterface\n    {\n        \$data = \$this->request->getJSON(true);\n        if (!\$this->model->insert(\$data)) {\n            return \$this->failValidationErrors(\$this->model->errors());\n        }\n        return \$this->respondCreated(['message' => '{$module} created', 'id' => \$this->model->getInsertID()]);\n    }\n\n    public function update(\$id = null): ResponseInterface\n    {\n        if (!\$this->model->find(\$id)) return \$this->failNotFound('{$module} not found');\n        \$data = \$this->request->getJSON(true);\n        \$this->model->update(\$id, \$data);\n        return \$this->respond(['message' => '{$module} updated']);\n    }\n\n    public function delete(\$id = null): ResponseInterface\n    {\n        if (!\$this->model->find(\$id)) return \$this->failNotFound('{$module} not found');\n        \$this->model->delete(\$id);\n        return \$this->respondDeleted(['message' => '{$module} deleted']);\n    }\n}\n";
    }

    private function modelTemplate(string $module): string
    {
        $table = 'cms_' . strtolower($module);
        return "<?php\nnamespace {$module}\\Models;\n\nuse CodeIgniter\\Model;\n\nclass {$module}Model extends Model\n{\n    protected \$table = '{$table}';\n    protected \$primaryKey = 'id';\n    protected \$returnType = 'array';\n    protected \$protectFields = true;\n    protected \$allowedFields = [];\n    protected \$useTimestamps = true;\n}\n";
    }
}
