<?php

/*
 * This file is part of the Doctrine DBAL Util package.
 *
 * (c) Jean-Bernard Addor
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalUtil\Connection;

use Doctrine\DBAL\Driver\Connection;

trait ConnectionTrait
{
    use ConnectionAbstractTrait;

    private $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
        // parent::__construct();
    }

    protected function getConnection()
    {
        return $this->dbal;
    }
}
