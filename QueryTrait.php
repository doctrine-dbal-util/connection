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

trait QueryTrait
{
    use ConnectionAbstractTrait;

    public function getWhereAndTraversable($table, array $where)
    // url/show
    {
        $qb = $this->getConnection()->createQueryBuilder();

        return $qb
            ->select('*')
            ->from($table)
            ->where(call_user_func_array([$qb->expr(), 'andX'],
                array_map(
                    [$qb->expr(), 'eq'],
                    array_keys($where),
                    array_map([$qb, 'createNamedParameter'], array_values($where))
                )
            ))
            ->execute();
        // TODO: check if $stmt should be ->execute() like in getByUnique
    }

    public function findUniqueBy($table, array $where)
    {
        return $this->getByUnique($table, $where);
        // should check for unicity
    }

    public function findFirstBy($table, array $where)
    {
        return $this->getByUnique($table, $where);
        // return first result
    }

    public function findOneBy($table, array $where)
    {
        return $this->getByUnique($table, $where);
        // not clear, should return first result
    }

    public function find($table, array $where)
    {
        return $this->getByUnique($table, $where);
        // should search by id
    }

    public function getByUnique($table, array $where)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $stmt = $qb
            ->select('*')
            ->from($table)
            ->where(call_user_func_array([$qb->expr(), 'andX'],
                array_map(
                    [$qb->expr(), 'eq'],
                    array_keys($where),
                    array_map([$qb, 'createNamedParameter'], array_values($where))
                )
            ))
            ->execute();
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()) {
                return $row;
            }
        } else {
            die('tbS8k: '.basename(__FILE__)); // TODO
        }
        // TODO: check if something should be ended or close...
    }

    public function insert($table, array $insert)
    { // TODO: (SECURITY) assert $insert is an array DONE
        $this->getConnection()->insert($table, $insert);
        // The construct with the array triggers a prepared statement
    }

    /*
    public function insert_default_values($table) {
        $this->getConnection()->executeUpdate('INSERT INTO ' . $table . ' DEFAULT VALUES');
    }
    */

    public function lastInsertId(string $seqName = null)
    { // used?
        $this->getConnection()->lastInsertId($seqName);
    }

    public function updateUniqueBy($table, array $id, array $row, array $types = [])
    {
        return $this->updateByUnique($table, $id, $row, $types);
    }

    public function updateByUnique($table, array $id, array $row, array $types = [])
    { // TODO: assert unicity of index
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->update($table)->where(call_user_func_array([$qb->expr(), 'andX'],
            array_map(
                [$qb->expr(), 'eq'],
                array_keys($id),
                array_map([$qb, 'createNamedParameter'], array_values($id))
            )
        ));
        foreach ($row as $key => $value) {
            $qb->set($key, ':'.$key);
            if (array_key_exists($key, $types)) {
                $qb->setParameter(':'.$key, $value, $types[$key]);
            } else {
                $qb->setParameter(':'.$key, $value);
            }
        }
        $qb->execute();
    }

    public function deleteByUnique($table, array $id)
    { // TODO: assert unicity of index
        $qb = $this->getConnection()->createQueryBuilder();

        $qb
            ->delete($table)
            ->where(call_user_func_array([$qb->expr(), 'andX'],
                array_map(
                    [$qb->expr(), 'eq'],
                    array_keys($id),
                    array_map([$qb, 'createNamedParameter'], array_values($id))
                )
            ))
            ->execute()
        ;
    }

    public function getManyToManyTraversable($base_table, $base_id, $link_base_id, $link_table, $link_distant_id, $distant_id, $distant_table, array $unique)
    // url/show
    {
        $qb = $this->getConnection()->createQueryBuilder();
        return $qb
            ->select('d.*', 'l.*') // TODO: name collision RISK: seems to that data with same column name is taken from second (may be not reliable)
            // ->select('d.*') // TODO: name collision RISK: seems to that data with same column name is taken from second (may be not reliable): to try...
            ->from($base_table, 'b')
            ->innerJoin('b', $link_table, 'l', 'b.'.$base_id.' = l.'.$link_base_id)
            ->innerJoin('l', $distant_table, 'd', 'l.'.$link_distant_id.' = d.'.$distant_id)
            // ->where('b.id = '.$queryBuilder->createPositionalParameter($id))
            ->where(call_user_func_array([$qb->expr(), 'andX'],
                array_map(
                    [$qb->expr(), 'eq'],
                    array_map(function ($s) {return 'b.'.$s; }, array_keys($unique)),
                    array_map([$qb, 'createNamedParameter'], array_values($unique))
                )
            ))
            ->execute()
        ;
    }

    public function sqlarray2dbal(array $sqlTree, $qb)
    {
        // $conn = $this->getConnection();
        // $qb = $this->getConnection()->createQueryBuilder();
        // $qb = $conn->createQueryBuilder();
        // dump($sqlTree);
        foreach ($sqlTree as $key => $value):
            /// dump($key, $value);
            switch ($key) {
                case 'select':
                    $qb->select($value);
                    break;
                case 'from':
                    if (1 != count($value)):
                        0/0; // TODO
                    endif;
                    $qb->from(array_keys($value)[0], array_values($value)[0]);
                    break;
                case 'where':
                    0/0; // TODO
                    break;
                case 'join':
                case 'innerJoin':
                    foreach ($value as    $key => $jvalue) {
                        if (1 != count($jvalue['table'])) {
                            0/0; // TODO
                        }
                        // dump          ($jvalue['from'], array_keys($jvalue['table'])[0], array_values($jvalue['table'])[0], $jvalue['on']);
                        $qb->innerJoin($jvalue['from'], array_keys($jvalue['table'])[0], array_values($jvalue['table'])[0], $jvalue['on']);
                    }
                    break;
                // default:
                    // echo "i n'est ni égal à 2, ni à 1, ni à 0";
            }
        endforeach;
        return $qb;
    }

    public function getManyToManyWhereQueryBuilder($base_table, $base_id,
        $link_base_id, $link_table, $link_distant_id,
        $distant_id, $distant_table, array $where)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        if (false): // ----------------------------------------------------
            $qa0 = [
                'select' => 'base.*',
                'from' => [
                    $base_table => 'base',
                    $link_table => 'link',
                ],
                'where' => [
                    'and' => [
                        '=' => [
                            'base.'.$base_id,
                            'link.'.$link_base_id,
                        ],
                        '<' => [1, 2],
                    ],
                ],
            ];
            $qa1 = [
                'select' => 'base.*',
                'from' => [
                    $base_table => 'base',
                    'tablex' => ['alias' => 'x'],
                    'other' => [
                        'alias' => 'o',
                        'innerJoin' => [
                            'table' => [$link_table => 'link'],
                            'on' => [
                                '=' => [
                                    'base.'.$base_id,
                                    'link.'.$link_base_id,
                                ],
                            ],
                        ],
                    ],
                ],
                'innerJoin' => [
                    'base' => [
                        'table' => [$link_table => 'link'],
                        'on' => [
                            '=' => [
                                'base.'.$base_id,
                                'link.'.$link_base_id,
                            ],
                        ],
                    ],
                ],
                'innerJoin' => [
                    'from' => 'base',
                    'table' => [$link_table => 'link'],
                    'on' => [
                        '=' => [
                            'base.'.$base_id,
                            'link.'.$link_base_id,
                        ],
                    ],
                ],
                'where' => [
                    'and' => [
                        '=' => [1, 2],
                        '<' => [1, 2],
                    ],
                ],
            ];
            dump($qa0, $qa1);
            // TODO: make it constant (maybe for php7)
            // http://php.net/manual/en/language.constants.syntax.php
            // http://php.net/manual/en/language.oop5.constants.php    Class Constants
        endif;
        $result = $this->sqlarray2dbal([
                'select' => 'base.*',
                'from' => [
                    $base_table => 'base',
                ],
                'innerJoin' => [
                    [
                        'from' => 'base',
                        'table' => [$link_table => 'link'],
                        'on' => 'base."'.$base_id.'" = link."'.$link_base_id.'"',
                    ],
                    [
                        'from' => 'link',
                        'table' => [$distant_table => 'distant'],
                        'on' => 'link."'.$link_distant_id.'" = distant."'.$distant_id.'"',
                    ],
                ],
            ], $qb)
            // ->select('base.*')
            // ->from($base_table, 'base')
            // ->innerJoin('base', '"' . $link_table . '"', 'link', 'base."' . $base_id . '" = link."' . $link_base_id . '"')
            // ->innerJoin('link', '"' . $distant_table . '"', 'distant', 'link."' . $link_distant_id . '" = distant."'. $distant_id . '"')
            ->where(call_user_func_array([$qb->expr(), 'andX'],
                array_map(
                    [$qb->expr(), 'eq'],
                    array_map(function ($s) {return 'distant.'.$s; }, array_keys($where)),
                    array_map([$qb, 'createNamedParameter'], array_values($where))
                )
            ))
        ;
        return $result;
    }

    public function getWhereManyToManyToManyQueryBuilder(
        $base_table, $base_id, // $t0, $t0_id,...
        $base_link_base_id, $base_link_table, $base_link_distant_id,
        $distant_link_base_id, $distant_link_table, $distant_link_distant_id,
        $distant_id, $distant_table,
        array $where // ,
        // $orderby=''
        ) {
        $qb = $this->getConnection()->createQueryBuilder();
        $result = $qb
            ->select('distant.*, base."'.$base_id.'" as base_link_id, base_link."'.$base_link_distant_id.'" as distant_link_id')
            ->from($base_table, 'base')
            ->innerJoin('base', '"'.$base_link_table.'"', 'base_link', 'base."'.$base_id.'" = base_link."'.$base_link_base_id.'"')
            ->innerJoin('base_link', '"'.$distant_link_table.'"', 'distant_link', 'base_link."'.$base_link_distant_id.'" = distant_link."'.$distant_link_base_id.'"')
            ->innerJoin('distant_link', '"'.$distant_table.'"', 'distant', 'distant_link."'.$distant_link_distant_id.'" = distant."'.$distant_id.'"')
            ->where(call_user_func_array([$qb->expr(), 'andX'],
                array_map(
                    [$qb->expr(), 'eq'],
                    array_map(function ($s) {return 'base.'.$s; }, array_keys($where)),
                    array_map([$qb, 'createNamedParameter'], array_values($where))
                )
            ));
        // if ($orderby != ''):
        //     $result->orderBy($orderby);
        // endif;
        return $result;
    }

    public function getMoreManyToManyWhereQueryBuilder($more_table, $more_id, $base_more, $base_table, $base_id, $link_base_id, $link_table, $link_distant_id, $distant_id, $distant_table, array $where)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        return $qb
            ->select('more.*, base.*') // collision risk
            ->from($base_table, 'base')
            ->innerJoin('base', '"'.$more_table.'"', 'more', 'base."'.$base_more.'" = more."'.$more_id.'"')
            ->innerJoin('base', '"'.$link_table.'"', 'link', 'base."'.$base_id.'" = link."'.$link_base_id.'"')
            ->innerJoin('link', '"'.$distant_table.'"', 'distant', 'link."'.$link_distant_id.'" = distant."'.$distant_id.'"')
            ->where(call_user_func_array([$qb->expr(), 'andX'],
                array_map(
                    [$qb->expr(), 'eq'],
                    array_map(function ($s) {return 'distant.'.$s; }, array_keys($where)),
                    array_map([$qb, 'createNamedParameter'], array_values($where))
                )
            ))
        ;
    }

    public function getUrlIndexQueryBuilder($more_table, $more_id,
        $base_more, $base_table, $base_id,
        $link_base_id, $link_table, $link_distant_id,
        $distant_id, $distant_table, array $where)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        // return $qb
        return $this->sqlarray2dbal([
                'select' => 'more.*, base.*',
                'from' => [
                    $base_table => 'base',
                ],
                'innerJoin' => [
                    [
                        'from' => 'base',
                        'table' => [$more_table => 'more'],
                        'on' => 'base."'.$base_more.'" = more."'.$more_id.'"',
                    ],
                    [
                        'from' => 'base',
                        'table' => [$link_table => 'link'],
                        'on' => 'base."'.$base_id.'" = link."'.$link_base_id.'"',
                    ],
                    [
                        'from' => 'link',
                        'table' => [$distant_table => 'distant'],
                        'on' => 'link."'.$link_distant_id.'" = distant."'.$distant_id.'"',
                    ],
                ],
            ], $qb)
            // ->select('more.*, base.*, count(base.uuid=taxo.owned_url_uuid) AS taxocount') // collision risk!
            // ->select('more.*, base.*') // collision risk!
            // ->from($base_table, 'base')
            // ->innerJoin('base', '"' . $more_table . '"', 'more', 'base."' . $base_more . '" = more."' . $more_id . '"')
            // ->innerJoin('base', '"' . $link_table . '"', 'link', 'base."' . $base_id . '" = link."' . $link_base_id . '"')
            // ->innerJoin('link', '"' . $distant_table . '"', 'distant', 'link."' . $link_distant_id . '" = distant."'. $distant_id . '"')
            ->leftJoin('base', '"'.'link_owned_url_taxonomy'.'"', 'taxo', 'base."'.$base_id.'" = taxo."'.'owned_url_uuid'.'"')
            ->where(call_user_func_array([$qb->expr(), 'andX'],
                array_map(
                    [$qb->expr(), 'eq'],
                    array_merge([], array_map(function ($s) {return 'distant.'.$s; }, array_keys($where))),
                    array_merge([], array_map([$qb, 'createNamedParameter'], array_values($where)))
                )
            ))
            // ->orderBy('taxocount', 'ASC')
        ;
    }
}

//^   NOTE: Just because you CAN use quoted identifiers doesn't mean you SHOULD use them. In general, they end up causing way more problems than they solve.
//^   Search for "quote" "quoteIdentifier" "quoteSingleIdentifier" "quoteStringLiteral" "getStringLiteralQuoteCharacter" in this page
//^   https://www.google.ca/search?q=quote+quoteIdentifier+quoteSingleIdentifier+quoteStringLiteral+getStringLiteralQuoteCharacter+site:www.doctrine-project.org/api/dbal
//^   https://www.google.ca/search?q=AbstractPlatform+quote+quoteIdentifier+quoteSingleIdentifier+quoteStringLiteral+getStringLiteralQuoteCharacter+site:www.doctrine-project.org/api/dbal
//^   $conn->getDatabasePlatform()->...
// Quoting of identifiers is SQL-dialect dependent (and differs between identifiers and literal values)
//^ https://stackoverflow.com/questions/22459092/pdo-postgresql-quoted-identifiers-in-where
//^ Postgres manual
//^ https://www.postgresql.org/docs/current/static/sql-syntax-lexical.html
// Quoting of values seems more or less similar in main SQL dialects
//^ https://www.w3schools.com/sql/sql_insert.asp
//^ https://www.postgresql.org/docs/current/static/dml-insert.html
