<?php

require_once __DIR__ . '/../autoload.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

use App\Core\Container;
use App\Core\Database;
use App\Core\Router;
use App\Models\NoteModel;
use App\Models\PracticeModel;
use App\Models\SubmissionModel;
use App\Models\UserModel;
use App\Core\Gate;
use App\Core\ValidationService;
use App\Core\FileService;
use App\Policies\UserPolicy;
use App\Policies\NotePolicy;
$container = new Container();
$container->set(Database::class, static function (Container $container): Database {
    $dbConfig = require __DIR__ . '/../config/database.php';

    return new Database(
        $dbConfig['dsn'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['options'] ?? []
    );
});

$container->set(UserModel::class, static function (Container $container): UserModel {
    return new UserModel($container->get(Database::class));
});

$container->set(NoteModel::class, static function (Container $container): NoteModel {
    return new NoteModel($container->get(Database::class));
});

$container->set(PracticeModel::class, static function (Container $container): PracticeModel {
    return new PracticeModel($container->get(Database::class));
});

$container->set(SubmissionModel::class, static function (Container $container): SubmissionModel {
    return new SubmissionModel($container->get(Database::class));
});

$container->set(Gate::class, static function (Container $container): Gate {
    $gate = new Gate();
    $gate->policy('user', UserPolicy::class);
    $gate->policy('note', NotePolicy::class);
    return $gate;
});

$container->set(ValidationService::class, static function (Container $container): ValidationService {
    return new ValidationService();
});

$container->set(FileService::class, static function (Container $container): FileService {
    return new FileService();
});

$routes = require __DIR__ . '/../config/routes.php';

$router = new Router($routes, $container);

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

?>
<code>
<?=  var_dump($_SERVER); ?>
</code>