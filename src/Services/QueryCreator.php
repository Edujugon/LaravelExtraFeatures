<?php
namespace Edujugon\LaravelExtraFeatures\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueryCreator
{

    /**
     * List of values what don't need to be compared. (whereNull,whereNotNull...)
     * @var array
     */
    private $withoutComparisonValues = ['null','NULL','notNull'];

    /**
     * list of values to be replaced.
     * @var array
     */
    private $replacements = [
        'null' => ['notNull']
    ];

    /**
     * list of operators based on their list.
     *
     * @var array
     */
    private $operators = [
        'is' => ['null','NULL'],
        'is not' => ['notNull']
    ];

    /**
     * Create a query dynamically with passed parameters.
     *
     * Params:
     *  tableName is the table name.
     *  array is a list of data to be converted to query.
     *
     * @param $tableName
     * @param array $array
     * @return mixed
     */
    static public function dynamic($tableName, array $array)
    {
        (new static)->tableExist($tableName);

        $query = DB::table($tableName);

        $query = (new static)->queryBuilder($query,$array);

        return $query;
    }

    /**
     * Check if the table exists in the schema
     *
     * @param $tableName
     * @throws \Exception
     */
    private function tableExist($tableName)
    {
        if(!Schema::hasTable($tableName))
            throw new \Exception('Table not found.');
    }

    /**
     * Build the query
     *
     * @param $query
     * @param $array
     * @return mixed
     */
    private function queryBuilder($query, $array)
    {
        foreach ($array as $key => $values)
        {
            $query = $this->setCorrectMethod($query,$key,$values);
        }

        return $query;
    }

    /**
     * Create the query depending on it need to be compareted or not.
     *
     * @param $query
     * @param $key
     * @param $values
     * @return mixed
     */
    private function setCorrectMethod($query, $key, $values)
    {
        if(in_array($key,$this->withoutComparisonValues)){
            foreach ($values as $val)
            {
                $operator = $this->getOperator($key);
                $key = $this->getReplacement($key);

                $query = $query->whereRaw("$val $operator $key");
                ;
            }
        }else{
            foreach ($values as $first => $second)
            {
                $query->whereRaw("$first $key $second");
            }
        }
        return $query;
    }


    /**
     * If passed value has a replacement the return that one otherwise return same value.
     *
     * @param $needle
     * @return int|string
     */
    private function getReplacement($needle)
    {
        foreach ($this->replacements as $key => $values)
        {
            if(in_array($needle,$values))
            {
                $needle = $key;
            }
        }
        return $needle;
    }

    /**
     * Set the correct operator for the statement.
     *
     * @param $val
     * @return int|string
     */
    private function getOperator($val)
    {
        $operator = '=';

        foreach ($this->operators as $key => $values)
        {
            if(in_array($val,$values))
            {
                $operator = $key;
            }
        }
        return $operator;
    }

}