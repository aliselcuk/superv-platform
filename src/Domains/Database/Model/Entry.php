<?php

namespace SuperV\Platform\Domains\Database\Model;

class Entry extends Model
{
    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \SuperV\Platform\Domains\Database\Model\EloquentQueryBuilder|static
     */
    public function newEloquentBuilder($query)
    {
        return new EloquentQueryBuilder($query);
    }

    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }

    /**
     * @param $id
     * @return static
     */
    public static function find($id)
    {
        return static::query()->find($id);
    }
}
