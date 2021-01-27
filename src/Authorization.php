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
     * @var Session
     */
    private Session $session;

    /**
     * Authorization constructor.
     * @param Database $database
     * @param Session $session
     */
    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
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
            'SELECT * FROM user WHERE email = :email'
        );

        /** @var TYPE_NAME $statement */
        $statement->execute([
            'email' => $data['email']
        ]);

        /** @var TYPE_NAME $user */
        $user = $statement->fetch();
        if (!empty($user)) {
            throw new AuthorizationException('User with such email is already exists');
        }

        //Username validating
        /** @var TYPE_NAME $statement */
        $statement = $this->database->getConnection()->prepare(
            'SELECT * FROM user WHERE username = :username'
        );

        /** @var TYPE_NAME $statement */
        $statement->execute([
            'username' => $data['username']
        ]);

        /** @var TYPE_NAME $statement */
        $user = $statement->fetch();
        if (!empty($user)) {
            throw new AuthorizationException('User with such username is already exists');
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
        return true;
    }

    /**
     * @param string $email
     * @param $password
     * @return bool
     * @throws AuthorizationException
     */
    public function login(string $email, $password): bool
    {
        if (empty($email)) {
            throw new AuthorizationException('Email should not be empty');
        }
        if (empty($password)) {
            throw new AuthorizationException('Password should not be empty');
        }

        $statement = $this->database->getConnection()->prepare(
            'SELECT * FROM user WHERE email = :email'
        );
        $statement->execute([
            'email' => $email
        ]);

        $user = $statement->fetch();

        if (empty($user)) {
            throw new AuthorizationException('User with such email is not found');
        }

        if (password_verify($password, $user['password'])) {
            $this->session->setData('user', [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
            ]);
            return true;
        }

        throw new AuthorizationException('Incorrect email or password');
    }
}