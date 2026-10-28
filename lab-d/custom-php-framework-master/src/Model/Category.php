<?php
namespace App\Model;

use App\Service\Config;

class Category
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Category
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Category
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Category
    {
        $this->description = $description;

        return $this;
    }

    public static function fromArray(array $array): Category
    {
        $category = new self();
        $category->fill($array);

        return $category;
    }

    public function fill(array $array): Category
    {
        if (isset($array['id']) && ! $this->getId() && is_numeric($array['id'])) {
            $this->setId((int) $array['id']);
        }
        if (array_key_exists('name', $array)) {
            $this->setName(is_scalar($array['name']) ? (string) $array['name'] : null);
        }
        if (array_key_exists('description', $array)) {
            $this->setDescription(is_scalar($array['description']) ? (string) $array['description'] : null);
        }

        return $this;
    }

    public static function findAll(): array
    {
        $pdo = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $statement = $pdo->prepare('SELECT * FROM category ORDER BY id');
        $statement->execute();

        $categories = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $categoryArray) {
            $categories[] = self::fromArray($categoryArray);
        }

        return $categories;
    }

    public static function find($id): ?Category
    {
        $pdo = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $statement = $pdo->prepare('SELECT * FROM category WHERE id = :id');
        $statement->execute(['id' => $id]);

        $categoryArray = $statement->fetch(\PDO::FETCH_ASSOC);
        if (! $categoryArray) {
            return null;
        }

        return self::fromArray($categoryArray);
    }

    public function save(): void
    {
        $pdo = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        if (! $this->getId()) {
            $statement = $pdo->prepare('INSERT INTO category (name, description) VALUES (:name, :description)');
            $statement->execute([
                'name' => $this->getName(),
                'description' => $this->getDescription(),
            ]);

            $this->setId((int) $pdo->lastInsertId());
        } else {
            $statement = $pdo->prepare('UPDATE category SET name = :name, description = :description WHERE id = :id');
            $statement->execute([
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'id' => $this->getId(),
            ]);
        }
    }

    public function delete(): void
    {
        $pdo = new \PDO(Config::get('db_dsn'), Config::get('db_user'), Config::get('db_pass'));
        $statement = $pdo->prepare('DELETE FROM category WHERE id = :id');
        $statement->execute(['id' => $this->getId()]);

        $this->setId(null);
        $this->setName(null);
        $this->setDescription(null);
    }
}
