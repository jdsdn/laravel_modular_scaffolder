# Laravel Modular Scaffolder
*Lean modular scaffolding for Laravel — generates models, controllers, services, validators, routes, migrations, and tests.*

---

## Requirements
- PHP 8.2+
- PHPUnit 10+
- Laravel 12+

---

## What it does
Generates a fully modular Laravel module with the following structure for a module named `Post`:

```
app/Modules/Post/
├─ Controllers/
│   └─ PostController.php
├─ Models/
│   └─ Post.php
├─ Services/
│   └─ PostService.php
├─ Validators/
│   └─ PostValidator.php
├─ Tests/
│ ├─ Feature/
│ │ └─ PostFeatureTest.php
│ └─ Unit/
│   └─ PostUnitTest.php
└─ routes.php
```

- All folders are scaffolded; tests are blank but ready to fill.  
- Routes are automatically lowercase in the stub.  
- Migrations are generated but typically kept in `database/migrations` for CI/CD compatibility.

---

## PHPUnit Setup
Add this to your `phpunit.xml` so module tests are detected:

```
<testsuite name="Module Tests">
    <directory suffix="Test.php">./app/Modules</directory>
</testsuite>
```

Make sure app/Modules exists (add .gitkeep if empty) so CI runners don’t fail.

### API Routes Setup

Include all module routes in routes/api.php:
```
foreach (glob(app_path('Modules/*/routes.php')) as $routeFile) {
    require $routeFile;
}
```
Keeps api.php clean. Automatically loads routes for every module.

### Installation
```
php artisan make:command MakeModule
```
Replace the generated command file with this repository’s MakeModule.php.

Make the changes to api.php and phpunit.xml as described above.

Generate your first module:
```
php artisan make:module Post
```

## License

[MIT](https://choosealicense.com/licenses/mit/)
