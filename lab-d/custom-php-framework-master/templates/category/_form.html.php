<?php

/** @var \App\Model\Category $category */
/** @var array $errors */
/** @var \App\Service\Router $router */

$formAction = $category->getId() ? 'category-edit' : 'category-create';
?>
<?php if (! empty($errors)): ?>
    <ul class="validation-errors">
        <?php foreach ($errors as $error): ?>
            <li><?= $escape($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form action="<?= $escape($router->generatePath($formAction)) ?>" method="post" class="edit-form">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="category[name]" value="<?= $escape($category->getName()) ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="category[description]"><?= $escape($category->getDescription()) ?></textarea>
    </div>

    <div class="form-group">
        <label></label>
        <input type="submit" value="Save">
    </div>
    <input type="hidden" name="action" value="<?= $escape($formAction) ?>">
    <?php if ($category->getId()): ?>
        <input type="hidden" name="id" value="<?= $escape($category->getId()) ?>">
    <?php endif; ?>
</form>
