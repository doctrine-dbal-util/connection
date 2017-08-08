<?php

namespace DbalUtil\Connection;

use Doctrine\DBAL\Driver\Connection;

trait ConnectionAbstractTrait // TODO: should this trait itself be made abstract?
{
    abstract protected function getConnection();
}
