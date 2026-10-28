<?php

/** @var \App\Model\Category $category */
/** @var \App\Service\Router $router */

$title = 'Category Details';
$bodyClass = 'show';
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

ob_start(); ?>
    <h1><?= $escape($category->getName()) ?></h1>
    <p><strong>ID:</strong> <?= $escape($category->getId()) ?></p>
    <article><?= nl2br($escape($category->getDescription())) ?></article>

    <?php if (! empty($confirmDelete)): ?>
        <p>To confirm deletion, submit the form below.</p>
    <?php endif; ?>

    <ul class="action-list">
        <li><a href="<?= $escape($router->generatePath('category-index')) ?>">Back to list</a></li>
        <li><a href="<?= $escape($router->generatePath('category-edit', ['id' => $category->getId()])) ?>">Edit</a></li>
        <li>
            <form action="<?= $escape($router->generatePath('category-delete')) ?>" method="post">
                <input type="hidden" name="action" value="category-delete">
                <input type="hidden" name="id" value="<?= $escape($category->getId()) ?>">
                <input type="submit" value="Delete" onclick="return confirm('Delete this category?')">
            </form>
        </li>
    </ul>
<?php $main = ob_get_clean();

include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'base.html.php';
