<?php

declare(strict_types=1);

namespace QueryMule\Query\Common\Sql;


use QueryMule\Query\Exception\SqlException;
use QueryMule\Query\Sql\Sql;
use QueryMule\Query\Sql\Statement\FilterInterface;

/**
 * Trait HasWhereExists
 * @package QueryMule\Query\Common\Sql
 */
trait HasWhereExists
{
    /**
     * @param Sql $subQuery
     * @return FilterInterface
     * @throws SqlException
     */
    public function whereExists(Sql $subQuery): FilterInterface
    {
        if($this instanceof FilterInterface) {
            $this->where(null, $this->query()->logical()->omitTrailingSpace()->exists($subQuery));

            return $this;
        }else {
            throw new SqlException(sprintf("Must invoke FilterInterface in: %s.", get_class($this)));
        }
    }
}