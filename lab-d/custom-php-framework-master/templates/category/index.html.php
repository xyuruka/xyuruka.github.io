<?php

/** @var \App\Model\Category[] $categories */
/** @var \App\Service\Router $router */

$title = 'Category List';
$bodyClass = 'index';
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

ob_start(); ?>
    <h1>Categories</h1>

    <a href="<?= $escape($router->generatePath('category-create')) ?>">Add category</a>

    <?php if (! $categories): ?>
        <p>No categories have been added yet.</p>
    <?php else: ?>
        <ul class="index-list">
            <?php foreach ($categories as $category): ?>
                <li>
                    <h3><?= $escape($category->getName()) ?></h3>
                    <p><?= nl2br($escape($category->getDescription())) ?></p>
                    <ul class="action-list">
                        <li><a href="<?= $escape($router->generatePath('category-show', ['id' => $category->getId()])) ?>">Details</a></li>
                        <li><a href="<?= $escape($router->generatePath('category-edit', ['id' => $category->getId()])) ?>">Edit</a></li>
                        <li>
                            <form action="<?= $escape($router->generatePath('category-delete')) ?>" method="post">
                                <input type="hidden" name="action" value="category-delete">
                                <input type="hidden" name="id" value="<?= $escape($category->getId()) ?>">
                                <input type="submit" value="Delete" onclick="return confirm('Delete this category?')">
                            </form>
                        </li>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

<?php $main = ob_get_clean();

include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'base.html.php';
