<?php

namespace QueryMule\Builder\Sql\Generic;

use QueryMule\Builder\Exception\SqlException;
use QueryMule\Query\Repository\RepositoryInterface;
use QueryMule\Query\Sql\Accent;
use QueryMule\Query\Sql\Clause\HasColumnClause;
use QueryMule\Query\Sql\Clause\HasFromClause;
use QueryMule\Query\Sql\Clause\HasJoinClause;
use QueryMule\Query\Sql\Query;
use QueryMule\Query\Sql\Sql;
use QueryMule\Query\Sql\Statement\FilterInterface;
use QueryMule\Query\Sql\Statement\SelectInterface;

/**
 * Class Select
 * @package QueryMule\Builder\Sql\Sqlite
 */
abstract class Select implements SelectInterface
{
    use Accent;
    use Query;

    use HasFromClause;
    use HasColumnClause;
    use HasJoinClause;

    /**
     * @var FilterInterface
     */
    protected $filter;

    /**
     * Select constructor.
     * @param array $cols
     * @param RepositoryInterface|null $table
     * @param null $accent
     */
    public function __construct(array $cols = [], RepositoryInterface $table = null, $accent = null)
    {
        if(!empty($cols)) {
            $this->cols($cols);
        }

        if(!empty($table)) {
            $this->from($table);
        }

        if(!empty($accent)) {
            $this->setAccent($accent);
        }

        $this->queryAdd(self::SELECT,new Sql(self::SELECT));
    }

    /**
     * @param bool $ignore
     * @return SelectInterface
     */
    public function ignoreAccent($ignore = true) : SelectInterface
    {
        $this->ignoreAccentSymbol($ignore);

        if(!empty($this->filter)) {
            $this->filter->ignoreAccent($ignore);
        }

        return $this;
    }

    /**
     * @param array $cols
     * @param null $alias
     * @return SelectInterface
     */
    public function cols($cols = [self::SQL_STAR], $alias = null) : SelectInterface
    {
        $i = 0;
        foreach($cols as $key => &$col){
            if((int)$key !== $i){
                $i++; //Increment only when we using int positions
            }

            $sql = $this->columnClause(
                ($col !== self::SQL_STAR) ? $this->addAccent($col) : $col,
                !empty($alias) ? $this->addAccent($alias) : $alias,
                ($key !== $i) ? $key : null,
                !empty($this->queryGet(self::COLS))
            );

            $this->queryAdd(self::COLS,$sql);
        }

        return $this;
    }

    /**
     * @param RepositoryInterface $table
     * @param null $alias
     * @return SelectInterface
     */
    public function from(RepositoryInterface $table, $alias = null) : SelectInterface
    {
        $this->queryAdd(self::FROM,$this->fromClause($table,$alias));

        $this->filter = $table->filter();

        return $this;
    }

    /**
     * @param array $table
     * @param null $first
     * @param null $operator
     * @param null $second
     * @return SelectInterface
     * @throws SqlException
     */
    public function leftJoin(array $table, $first = null, $operator = null, $second = null) : SelectInterface
    {
        $keys = array_keys($table);

        $alias = isset($keys[0]) ? $keys[0] : null;
        $table = isset($table[$keys[0]]) ? $table[$keys[0]] : null;

        if($table instanceof RepositoryInterface) {
            $this->queryAdd(self::JOIN,$this->joinClause(self::LEFT_JOIN,$table, $alias));
            return $this->on($first,$operator,$second);
        }else {
            throw new SqlException('Table must be instance of RepositoryInterface');
        }
    }

    /**
     * @param $first
     * @param null $operator
     * @param null $second
     * @return SelectInterface
     */
    public function on($first, $operator, $second) : SelectInterface
    {
        $this->queryAdd(self::JOIN,$this->onClause($first,$operator,$second, self::ON));

        return $this;
    }

    /**
     * @param $first
     * @param null $operator
     * @param null $second
     * @return SelectInterface
     */
    public function orOn($first, $operator = null, $second = null) : SelectInterface
    {
        $this->queryAdd(self::JOIN,$this->onClause($first,$operator,$second, self::OR));

        return $this;
    }

    public function rightJoin(){}

    public function crossJoin(){}

    public function innerJoin(){}

    public function outerJoin(){}

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @param $clause
     * @return SelectInterface
     */
    public function where($column, $operator = null, $value = null, $clause = self::WHERE) : SelectInterface
    {
        $this->filter->where($column,$operator,$value,$clause);

        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @return SelectInterface
     */
    public function orWhere($column, $operator = null, $value = null) : SelectInterface
    {
        $this->filter->orWhere($column,$operator,$value);

        return $this;
    }

    /**
     * @param array $clauses
     * @return Sql
     */
    public function build(array $clauses = [
        self::SELECT,
        self::COLS,
        self::FROM,
        self::JOIN,
        self::WHERE,
        self::GROUP,
        self::ORDER,
        self::HAVING,
        self::LIMIT
    ]) : Sql
    {
        if(in_array(self::WHERE,$clauses)) {
            $this->queryAdd(self::WHERE, $this->filter->build([self::WHERE]));
        }

        $sql = $this->queryBuild($clauses);

        $this->queryReset($clauses);

        return $sql;
    }
}