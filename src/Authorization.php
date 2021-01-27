<?php
declare(strict_types=1);
namespace App;


use PDOStatement;

class Authorization
{
    /**
     * @var Database|Databse
     */
    private Database $database;

    /**
     * Authorization constructor.
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @param array $data
     * @return bool
     * @throws AuthorizationException
     */
    public function register(array $data): bool
    {
        if (empty($data['username'])) {
            throw new AuthorizationException('Username should not be empty');
        }
        if (empty($data['email'])) {
            throw new AuthorizationException('Email should not be empty');
        }
        if (empty($data['password'])) {
            throw new AuthorizationException('Password should not be empty');
        }
        if ($data['password'] !==  $data['confirm_password']) {
            throw new AuthorizationException('Passwords are not equal');
        }

        /** @var TYPE_NAME $statement */
        $statement = $this->database->getConnection()->prepare(
            'INSERT INTO user (email, username, password) VALUES (:email, :username, :password)'
        );

        /** @var TYPE_NAME $statement */
          $statement->execute([
              'email' => $data['email'],
              'username' => $data['username'],
              'password' => password_hash($data['password'], PASSWORD_BCRYPT)
           ]);
//        $statement = (new \PDOStatement)->execute([
//            'email' => $data['email'],
//            'username' => $data['username'],
//            'password' => password_hash($data['password'], PASSWORD_BCRYPT)
//        ]);

        return true;
    }
}