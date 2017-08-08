<?php

namespace DbalUtil\Connection;

use Doctrine\DBAL\Driver\Connection;

trait ConnectionTrait
{
    use ConnectionAbstractTrait;

    private $dbal;

    public function __construct(Connection $dbal) {
       $this->dbal = $dbal;
       // parent::__construct();
    }

    protected function getConnection() { return $this->dbal; }
}
