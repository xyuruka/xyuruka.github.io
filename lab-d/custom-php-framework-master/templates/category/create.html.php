<?php

/** @var \App\Model\Category $category */
/** @var \App\Service\Router $router */

$title = 'Create Category';
$bodyClass = 'edit';
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

ob_start(); ?>
    <h1>Create Category</h1>
    <?php require __DIR__ . DIRECTORY_SEPARATOR . '_form.html.php'; ?>
    <a href="<?= $escape($router->generatePath('category-index')) ?>">Back to list</a>
<?php $main = ob_get_clean();

include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'base.html.php';
