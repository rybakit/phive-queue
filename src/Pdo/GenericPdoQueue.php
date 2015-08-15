<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Pdo;

use Phive\Queue\NoItemAvailableException;

class GenericPdoQueue extends PdoQueue
{
    /**
     * @var array
     */
    protected static $popSqls = [
        'pgsql'     => 'SELECT item FROM %s(%d)',
        'firebird'  => 'SELECT item FROM %s(%d)',
        'informix'  => 'EXECUTE PROCEDURE %s(%d)',
        'mysql'     => 'CALL %s(%d)',
        'cubrid'    => 'CALL %s(%d)',
        'ibm'       => 'CALL %s(%d)',
        'oci'       => 'CALL %s(%d)',
        'odbc'      => 'CALL %s(%d)',
    ];

    /**
     * @var string
     */
    private $routineName;

    public function __construct(\PDO $pdo, $tableName, $routineName = null)
    {
        parent::__construct($pdo, $tableName);

        $this->routineName = $routineName ?: $this->tableName.'_pop';
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $stmt = $this->pdo->query($this->getPopSql());
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        if (false === $result) {
            throw new NoItemAvailableException($this);
        }

        return $result;
    }

    protected function supportsDriver($driverName)
    {
        return isset(static::$popSqls[$driverName]);
    }

    protected function getPopSql()
    {
        return sprintf(
            static::$popSqls[$this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)],
            $this->routineName,
            time()
        );
    }
}
