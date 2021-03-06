<?php

declare(strict_types=1);

namespace Redstraw\Hooch\Builder\Mysql;


use Redstraw\Hooch\Query\Common\HasOperator;
use Redstraw\Hooch\Query\Common\HasQuery;
use Redstraw\Hooch\Query\Operator;
use Redstraw\Hooch\Query\Common\Join\HasInnerJoin;
use Redstraw\Hooch\Query\Common\Join\HasJoin;
use Redstraw\Hooch\Query\Common\Join\HasLeftJoin;
use Redstraw\Hooch\Query\Common\Join\HasRightJoin;
use Redstraw\Hooch\Query\Common\Update\HasSet;
use Redstraw\Hooch\Query\Common\Update\HasTable;
use Redstraw\Hooch\Query\Query;
use Redstraw\Hooch\Query\Sql;
use Redstraw\Hooch\Query\Statement\FilterInterface;
use Redstraw\Hooch\Query\Statement\JoinInterface;
use Redstraw\Hooch\Query\Statement\OnFilterInterface;
use Redstraw\Hooch\Query\Statement\UpdateInterface;

/**
 * Class Update
 * @package Redstraw\Hooch\Builder\Sql\Mysql
 */
class Update implements UpdateInterface
{
    use HasQuery;
    use HasOperator;
    use HasSet;
    use HasTable;
    use HasJoin;
    use HasLeftJoin;
    use HasRightJoin;
    use HasInnerJoin;

    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @var OnFilterInterface
     */
    private $onFilter;

    /**
     * Update constructor.
     * @param Query $query
     * @param Operator $operator
     */
    public function __construct(Query $query, Operator $operator)
    {
        $this->operator = $operator;
        $this->query = $query;
        $this->query->clause(Sql::UPDATE, function(Sql $sql){
            return $sql->append(Sql::UPDATE);
        });
    }

    /**
     * @param array $clauses
     * @return Sql
     */
    public function build(array $clauses = [
        Sql::UPDATE,
        Sql::JOIN,
        Sql::SET,
        Sql::WHERE
    ]): Sql
    {
        if (in_array(Sql::WHERE, $clauses) && !empty($this->filter)) {
            $this->query->clause(Sql::WHERE, function(Sql $sql){
                return $sql->append($this->filter->build([Sql::WHERE]));
            });
        }

        if (in_array(Sql::JOIN, $clauses) && !empty($this->onFilter)) {
            $this->query->clause(Sql::JOIN, function(Sql $sql){
                return $sql->append($this->onFilter->build([Sql::JOIN]));
            });
        }

        $sql = $this->query->build($clauses);

        $this->query->reset($clauses);

        return $sql;
    }

    /**
     * @param string $column
     * @param int $amount
     * @return UpdateInterface
     * @throws \Redstraw\Hooch\Query\Exception\InterfaceException
     */
    public function increment(string $column, int $amount): UpdateInterface
    {
        $this->set([$column=>$column."+".$amount]);

        return $this;
    }

    /**
     * @param string $column
     * @param int $amount
     * @return UpdateInterface
     * @throws \Redstraw\Hooch\Query\Exception\InterfaceException
     */
    public function decrement(string $column, int $amount): UpdateInterface
    {
        $this->set([$column=>$column."-".$amount]);

        return $this;
    }

    /**
     * @param \Closure $callback
     * @return UpdateInterface
     */
    public function filter(\Closure $callback): UpdateInterface
    {
        if(!empty($this->filter)){
            $callback->call($this->filter, $this->table, ...$this->joinTables);
        }

        return $this;
    }

    /**
     * @param \Closure $callback
     * @return JoinInterface
     */
    public function onFilter(\Closure $callback): JoinInterface
    {
        if(!empty($this->onFilter)){
            $callback->call($this->onFilter, $this->table);
        }

        return $this;
    }

    /**
     * @param FilterInterface $filter
     */
    public function setFilter(FilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @param OnFilterInterface $onFilter
     */
    public function setOnFilter(OnFilterInterface $onFilter): void
    {
        $this->onFilter = $onFilter;
    }
}