<?php
namespace App\Controller;

use App\Exception\NotFoundException;
use App\Model\Category;
use App\Service\Router;
use App\Service\Templating;

class CategoryController
{
    public function indexAction(Templating $templating, Router $router): ?string
    {
        return $templating->render('category/index.html.php', [
            'categories' => Category::findAll(),
            'router' => $router,
        ]);
    }

    public function createAction(?array $requestPost, Templating $templating, Router $router): ?string
    {
        $category = $requestPost === null ? new Category() : Category::fromArray($requestPost);
        $errors = [];

        if ($requestPost !== null) {
            $errors = $this->validate($category);
            if (! $errors) {
                $category->setName(trim($category->getName()));
                $category->save();
                $router->redirect($router->generatePath('category-index'));

                return null;
            }
        }

        return $templating->render('category/create.html.php', [
            'category' => $category,
            'errors' => $errors,
            'router' => $router,
        ]);
    }

    public function editAction(int $categoryId, ?array $requestPost, Templating $templating, Router $router): ?string
    {
        $category = $this->findCategory($categoryId);

        $errors = [];
        if ($requestPost !== null) {
            $category->fill($requestPost);
            $errors = $this->validate($category);
            if (! $errors) {
                $category->setName(trim($category->getName()));
                $category->save();
                $router->redirect($router->generatePath('category-index'));

                return null;
            }
        }

        return $templating->render('category/edit.html.php', [
            'category' => $category,
            'errors' => $errors,
            'router' => $router,
        ]);
    }

    public function showAction(int $categoryId, Templating $templating, Router $router): ?string
    {
        $category = $this->findCategory($categoryId);

        return $templating->render('category/show.html.php', [
            'category' => $category,
            'router' => $router,
        ]);
    }

    public function deleteAction(
        int $categoryId,
        string $requestMethod,
        Templating $templating,
        Router $router
    ): ?string {
        $category = $this->findCategory($categoryId);

        if (strtoupper($requestMethod) !== 'POST') {
            return $templating->render('category/show.html.php', [
                'category' => $category,
                'router' => $router,
                'confirmDelete' => true,
            ]);
        }

        $category->delete();
        $router->redirect($router->generatePath('category-index'));

        return null;
    }

    private function validate(Category $category): array
    {
        $errors = [];
        if ($category->getName() === null || trim($category->getName()) === '') {
            $errors['name'] = 'Nazwa kategorii nie może być pusta.';
        }
        if ($category->getDescription() === null) {
            $errors['description'] = 'Opis musi być tekstem.';
        }

        return $errors;
    }

    private function findCategory(int $categoryId): Category
    {
        $category = Category::find($categoryId);
        if (! $category) {
            http_response_code(404);
            throw new NotFoundException("Missing category with id $categoryId");
        }

        return $category;
    }
}
