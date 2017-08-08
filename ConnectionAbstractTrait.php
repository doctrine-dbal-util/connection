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

trait ConnectionAbstractTrait // TODO: should this trait itself be made abstract?
{
    abstract protected function getConnection();
}
