<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';

$config = new \App\Service\Config();

$templating = new \App\Service\Templating();
$router = new \App\Service\Router();

$action = $_REQUEST['action'] ?? null;
switch ($action) {
    case 'post-index':
    case null:
        $controller = new \App\Controller\PostController();
        $view = $controller->indexAction($templating, $router);
        break;
    case 'post-create':
        $controller = new \App\Controller\PostController();
        $view = $controller->createAction($_REQUEST['post'] ?? null, $templating, $router);
        break;
    case 'post-edit':
        if (! $_REQUEST['id']) {
            break;
        }
        $controller = new \App\Controller\PostController();
        $view = $controller->editAction($_REQUEST['id'], $_REQUEST['post'] ?? null, $templating, $router);
        break;
    case 'post-show':
        if (! $_REQUEST['id']) {
            break;
        }
        $controller = new \App\Controller\PostController();
        $view = $controller->showAction($_REQUEST['id'], $templating, $router);
        break;
    case 'post-delete':
        if (! $_REQUEST['id']) {
            break;
        }
        $controller = new \App\Controller\PostController();
        $view = $controller->deleteAction($_REQUEST['id'], $router);
        break;
    case 'category-index':
        $controller = new \App\Controller\CategoryController();
        $view = $controller->indexAction($templating, $router);
        break;
    case 'category-create':
        $controller = new \App\Controller\CategoryController();
        $requestPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            ? (is_array($_POST['category'] ?? null) ? $_POST['category'] : [])
            : null;
        $view = $controller->createAction($requestPost, $templating, $router);
        break;
    case 'category-edit':
    case 'category-show':
    case 'category-delete':
        $categoryId = $_REQUEST['id'] ?? null;
        if (! is_scalar($categoryId) || (int) $categoryId <= 0) {
            $view = 'Not found';
            break;
        }

        $controller = new \App\Controller\CategoryController();
        if ($action === 'category-edit') {
            $requestPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
                ? (is_array($_POST['category'] ?? null) ? $_POST['category'] : [])
                : null;
            $view = $controller->editAction((int) $categoryId, $requestPost, $templating, $router);
        } elseif ($action === 'category-show') {
            $view = $controller->showAction((int) $categoryId, $templating, $router);
        } else {
            $view = $controller->deleteAction(
                (int) $categoryId,
                $_SERVER['REQUEST_METHOD'] ?? 'GET',
                $templating,
                $router
            );
        }
        break;
    case 'info':
        $controller = new \App\Controller\InfoController();
        $view = $controller->infoAction();
        break;
    default:
        $view = 'Not found';
        break;
}

if ($view) {
    echo $view;
}
