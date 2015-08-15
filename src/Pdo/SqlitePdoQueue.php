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

class SqlitePdoQueue extends PdoQueue
{
    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $sql = sprintf(
            'SELECT id, item FROM %s WHERE eta <= %d ORDER BY eta LIMIT 1',
            $this->tableName,
            time()
        );

        $this->pdo->exec('BEGIN IMMEDIATE');

        try {
            $stmt = $this->pdo->query($sql);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($row) {
                $sql = sprintf('DELETE FROM %s WHERE id = %d', $this->tableName, $row['id']);
                $this->pdo->exec($sql);
            }

            $this->pdo->exec('COMMIT');
        } catch (\Exception $e) {
            $this->pdo->exec('ROLLBACK');
            throw $e;
        }

        if ($row) {
            return $row['item'];
        }

        throw new NoItemAvailableException($this);
    }

    protected function supportsDriver($driverName)
    {
        return 'sqlite' === $driverName;
    }
}
