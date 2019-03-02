<?php

declare(strict_types=1);

namespace Redstraw\Hooch\Query\Statement;


use Redstraw\Hooch\Query\Field\FieldInterface;
use Redstraw\Hooch\Query\Operator\OperatorInterface;

/**
 * Interface OnFilterInterface
 * @package Redstraw\Hooch\Query\Sql\Statement
 */
interface OnFilterInterface extends FilterInterface
{
    /**
     * @param FieldInterface|\Closure $column
     * @param OperatorInterface|null $operator
     * @return OnFilterInterface
     */
    public function on($column, ?OperatorInterface $operator): OnFilterInterface;

    /**
     * @param FieldInterface|\Closure $column
     * @param OperatorInterface|null $operator
     * @return OnFilterInterface
     */
    public function orOn($column, ?OperatorInterface $operator): OnFilterInterface;
}