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

use Phive\Queue\Queue;
use Phive\Queue\QueueUtils;

abstract class PdoQueue implements Queue
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $tableName;

    public function __construct(\PDO $pdo, $tableName)
    {
        $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if (!$this->supportsDriver($driverName)) {
            throw new \InvalidArgumentException(sprintf(
                'PDO driver "%s" is unsupported by "%s".',
                $driverName,
                get_class($this)
            ));
        }

        $this->pdo = $pdo;
        $this->tableName = $tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $sql = sprintf('INSERT INTO %s (eta, item) VALUES (%d, %s)',
            $this->tableName,
            QueueUtils::normalizeEta($eta),
            $this->pdo->quote($item)
        );

        $this->pdo->exec($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM '.$this->tableName);
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->pdo->exec('DELETE FROM '.$this->tableName);
    }

    /**
     * @param string $driverName
     *
     * @return bool
     */
    abstract protected function supportsDriver($driverName);
}
