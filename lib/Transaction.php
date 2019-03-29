<?php

namespace MOCUtils\Helpers;

use App\Http\Models\Auth\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Implementation.
 */
class Transaction
{
    /**
     * Current transaction's database.
     * @var string
     */
    protected $db;

    /**
     * Transaction result.
     * @var array
     */
    protected $results;

    /**
     * SQL Query error.
     * @var mixed
     */
    protected $error;

    /**
     * Transacion
     * Responsible to holds transaction behave.
     * @param mixed $closure
     */
    public function __construct($closure = null)
    {
        if (is_callable($closure)) {

            $connection = $this->getConnection();

            try {
                $connection->beginTransaction();

                $this->results = $closure($connection);

                if ($this->results === false) {
                    $connection->rollBack();
                } else {
                    $connection->commit();
                }
            } catch (\Exception $e) {
                $connection->rollBack();
                $this->error = $e;
            }
        }
        return $this;
    }

    /**
     * Gets current connection.
     * @param  string $name
     * @return \PDO
     */
    public function getConnection($name = null): \PDO
    {
        $pdo = DB::connection()->getPdo();

        return $pdo;
    }

    /**
     * Gets query results.
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Gets transacion's error.
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Checks if transacion has error.
     * @return boolean
     */
    public function hasError()
    {
        return isset($this->error);
    }
}
