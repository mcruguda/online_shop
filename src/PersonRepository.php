<?php

namespace App;

use PDO;

class PersonRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?Person
    {
        $statement = $this->pdo->prepare(
            "SELECT name, birthday, email FROM persons WHERE email = :email"
        );
        $statement->execute(["email" => strtolower($email)]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return new Person($row["name"], $row["birthday"], $row["email"]);
    }

    public function save(Person $person): bool
    {
        $existing = $this->findByEmail($person->getEmail());

        if ($existing === null) {
            $statement = $this->pdo->prepare(
                "INSERT INTO persons (email, name, birthday) VALUES (:email, :name, :birthday)"
            );
            $statement->execute([
                "email" => $person->getEmail(),
                "name" => $person->getName(),
                "birthday" => $person->getBirthday(),
            ]);

            return true;
        }

        $statement = $this->pdo->prepare(
            "UPDATE persons SET name = :name, birthday = :birthday WHERE email = :email"
        );
        $statement->execute([
            "name" => $person->getName(),
            "birthday" => $person->getBirthday(),
            "email" => $person->getEmail(),
        ]);

        return false;
    }

    public function delete(string $email): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM persons WHERE email = :email");
        $statement->execute(["email" => strtolower($email)]);

        return $statement->rowCount() > 0;
    }
}
