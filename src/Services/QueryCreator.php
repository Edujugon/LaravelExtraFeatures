<?php
namespace Edujugon\LaravelExtraFeatures\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueryCreator
{

    /**
     * list of values to be replaced.
     *
     * Key is the pattern and the list will be converted to the key value.
     *
     * @var array
     */
    private $replacements = [
        'null' => ['notNull']
    ];

    /**
     * list of operators based on a list.
     *
     * Key is the pattern and will be replaced as operator for the query statement
     *
     * @var array
     */
    private $operators = [
        'is' => ['null','NULL'],
        'is not' => ['notNull']
    ];

    /**
     * DB table name
     * @var
     */
    private $table;

    /**
     * DB Query
     * @var
     */
    private $query;

    /**
     * Run Query
     *
     * @return mixed
     */
    public function get()
    {
        return $this->query->get();
    }


    /**
     * Get the object query
     *
     * @return mixed
     */
    public function query()
    {
        return $this->query;
    }


    /**
     * Create a query dynamically based on passed parameters.
     *
     * Params:
     *  tableName is the table name.
     *  array is a list of data to be converted to query.
     * @param $tableName
     * @param array $array
     * @return mixed
     */
    public function build(array $array)
    {
        $this->walkArray($array);

        return $this;
    }

    /**
     * Set the table for the query
     *
     * @param $name
     * @return mixed
     */
    public static function table($name)
    {
        $instance = (new static)->tableExist($name);

        $instance->table = $name;

        return $instance;
    }

    /**
     * It's an alias where you can set table and build methods at once.
     *
     * @param $tableName
     * @param $array
     * @return mixed
     */
    public static function create($tableName, $array){
        $instance = (new static)->tableExist($tableName);

        $instance->walkArray($array);

        return $instance;
    }

    /**
     * Check if the table exists in the schema
     * If no exists, throw an exception.
     *
     * @param $tableName
     * @return $this
     * @throws \Exception
     */
    private function tableExist($tableName)
    {
        if(!Schema::hasTable($tableName))
            throw new \Exception('Table not found.');

        $this->query = DB::table($tableName);

        return $this;
    }

    /**
     * Walk the array and call setCorrectMethod for each statement.
     *
     * @param $array
     * @return mixed
     * @internal param $query
     */
    private function walkArray($array)
    {
        foreach ($array as $key => $values)
        {
            $this->setCorrectMethod($key,$values);
        }

    }


    /**
     * Set the correct method based on the array is associative or not.
     *
     * @param $key
     * @param $values
     */
    private function setCorrectMethod($key, $values)
    {
        if($this->isAssociative($values)){
            $this->compareParameters($key,$values);
        }else
            $this->singleAssignament($key,$values);
    }

    /**
     * Create a query comparing the 2 values of the child array.
     *
     * @param $key
     * @param $values
     */
    private function compareParameters($key, $values)
    {
        foreach ($values as $first => $second)
        {
            $this->query = $this->query->whereRaw("$first $key $second");
        }
    }

    /**
     * Create a query with he single value of the child array.
     *
     * @param $key
     * @param $values
     */
    private function singleAssignament($key, $values)
    {
        foreach ($values as $val)
        {
            $operator = $this->getOperator($key);
            $key = $this->getReplacement($key);

            $this->query = $this->query->whereRaw("$val $operator $key");
        }
    }

    /**
     * If passed value has a replacement then return it, otherwise return same value.
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

    /**
     * Confirm if array is associative or not.
     *
     * @param array $arr
     * @return bool
     */
    private function isAssociative(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}