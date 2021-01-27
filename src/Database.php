<?php
declare(strict_types=1);

namespace App;

use http\Exception\InvalidArgumentException;
use PDOException;
use PDO;

class Database
{
    /**
     * @var PDO
     */
    private PDO $connection;

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     */
    public function __constructor(string $dsn, string $username = '', string $password = '')
    {
        try {
            $this->connection = new PDO($dsn, $username, $password);
        } catch (PDOException $exp) {
            throw new InvalidArgumentException('Database connection error: ', $exp->getMessage());
        }
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}