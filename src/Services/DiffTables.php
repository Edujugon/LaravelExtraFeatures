<?php
namespace Edujugon\LaravelExtraFeatures\Services;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiffTables
{

    /**
     * Table name list to check the differences.
     * @var array
     */
    protected $tables=[];

    /**
     * Base table - Main table
     * @var string
     */
    protected $baseTable= '';

    /**
     * Collection of items from base table.
     *
     * @var
     */
    protected $baseCollection;


    /**
     * Table to be merged into the baseTable
     * @var string
     */
    protected $mergeTable= '';

    /**
     * Collection of items from Merge table.
     *
     * @var
     */
    protected $mergeCollection;


    /**
     * Associative array with base table pivot => merge table pivot
     * The key is the base table pivot and the value is the merge table pivot
     *
     *
     * @var array
     */
    protected $associativePivots = [];

    /**
     * Associative array with base table columns => merge table columns
     * The key is the base column and the value is the merge table column
     *
     * If empty, it will be search column name matching.
     *
     * @var array
     */
    protected $associativeColumns = [];

    /**
     * Collection of items that are in merge Table but no in base table
     * @var
     */
    protected $unMatchedCollection;

    /**
     * Collection of items that are in merge Table and in base table
     * @var
     */
    protected $matchedCollection;


    /**
     * stdClass object with the columns as properties.
     * each property will be an array with old and new values. (oldValue => newValue)
     *
     * @var
     */
    protected $report;


    /**
     * Primary Key. this column won't be overwritten except if passed as column
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Amount of rows updated by matched after the merge.
     *
     * @var int
     */
    protected $rowUpdatedByMatched = 0;

    /**
     * @var int
     */
    protected $rowInserted = 0;

    //
    //API METHODS
    //

    /**
     * Set table names.
     *
     * @param $baseTable
     * @param $mergeTable
     * @return static
     */
    public static function tables($baseTable,$mergeTable)
    {
        $instance = (new static());
        $instance->validateTables([$baseTable,$mergeTable]);

        $instance->setClassTableProperties([$baseTable,$mergeTable]);

        return $instance;
    }

    /**
     * Set pivots column.
     *
     * @param $basePivot
     * @param $mergePivot
     * @return $this
     */
    public function pivots($basePivot,$mergePivot)
    {
        return $this->multiPivots([$basePivot => $mergePivot]);

    }

    /**
     * Assign multi pivots.
     * Passed associative array with basePivot => mergePivot
     *
     * @param array $pivots
     * @return $this
     */
    public function multiPivots(array $pivots)
    {

        $this->validateAssociativeColumns($pivots);

        $this->setClassAssociativePivots($pivots);

        return $this;
    }

    /**
     * Set base and merge table pivots from one single pivot.
     *
     * @param $pivot
     * @return DiffTables
     */
    public function pivot($pivot)
    {
        return $this->pivots($pivot,$pivot);
    }

    /**
     * Load collections from tables
     * Get new elements from merge table and base table.
     * Get differences between both tables.
     *
     * @return $this
     */
    public function run(){

        $this->loadCollections();

        $this->loadMatchedCollection();
        $this->loadUnMatchedCollection();

        $this->initReport();

        return $this;
    }

    /**
     * Load the associative columns of base table and merge table.
     *
     * base table column1 => merge table column 10
     * base table column20 => merge table column 4
     * ...
     *
     * @param array $columns
     * @return $this
     */
    public function columns(array $columns)
    {
        $this->associativeColumns = $columns;

        $this->validateAssociativeColumns($columns);

        return $this;
    }

    /**
     * Return the report.
     *
     * @return mixed
     */
    public function withReport()
    {
        return $this->report;
    }

    /**
     * Return the report.
     *
     * @return mixed
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Return basic report.
     * Name of the column and amount of changes.
     *
     */
    public function withBasicReport()
    {
        return $this->basicReport();
    }

    /**
     * Alias for WithBasicReport
     */
    public function getBasicReport()
    {
        return $this->basicReport();
    }

    /**
     * Update Base table items with Merge table values.
     * Also Insert new values from merge table to base table.
     *
     * @return $this
     */
    public function merge(){

        $this->mergeMatched();
        $this->mergeUnMatched();

        return $this;
    }

    /**
     * Insert new values that are in merge table but not in base table.
     *
     * @return $this
     */
    public function mergeUnMatched()
    {
        $this->createNewColumnsInBaseTable();

        $this->insertUnMatched();

        return $this;
    }

    /**
     * Update Base table values with Matched Merge table values.
     * !Notice it won't update the new items found in merge that are not in base table.
     *
     * @return $this
     */
    public function mergeMatched()
    {
        $this->createNewColumnsInBaseTable();

        $this->updateBaseTable();

        return $this;
    }


    //GETTERS

    public function matched()
    {
        return $this->matchedCollection;
    }

    public function unMatched()
    {
        return $this->unMatchedCollection;
    }

    public function updatedRows()
    {
        return $this->rowUpdatedByMatched;
    }

    public function insertedRows()
    {
        return $this->rowInserted;
    }

    //

    //
    //PRIVATE METHODS
    //


    /**
     * Set the class Associative properties.
     *
     * @param array $pivots
     */
    private function setClassAssociativePivots(array $pivots)
    {
        $this->associativePivots = $pivots;
    }


    /**
     * Return column's name and amount of changes.
     */
    private function basicReport()
    {
        $basic = new \stdClass();
        foreach ($this->report as $key => $value)
        {
            $basic->$key = count($value);
        }

        return $basic;
    }

    /**
     * Create columns in a table if columns don't exist
     *
     */
    private function createNewColumnsInBaseTable()
    {

        Schema::table($this->baseTable, function (Blueprint $table) {

            //If no associative columns, the use same column name as merge column.
            if(empty($this->associativeColumns))
            {
                $columns = Schema::getColumnListing($this->mergeTable);

                foreach($columns as $key){
                    if(!Schema::hasColumn($table->getTable(),$key)) {
                        $table->text($key)->nullable();
                    }
                }
                //Otherwise create only the table columns in baseTable which appears in array.
            }else{
                foreach ($this->associativeColumns as $baseColumn => $mergeColumn)
                {
                    if(!Schema::hasColumn($table->getTable(),$baseColumn)) {
                        $table->text($baseColumn)->nullable();
                    }
                }
            }

        });
    }



    /**
     * Update values based on the matched collection.
     *
     */
    private function updateBaseTable()
    {
        foreach ($this->mergeCollection as $key => $item)
        {
            $query = DB::table($this->baseTable);

            foreach ($this->associativePivots as $basePivot => $mergePivot)
            {
                $query = $query->whereIn($basePivot,[$item->$mergePivot]);
            }

            if(!in_array($this->primaryKey,$this->associativeColumns) && !in_array($this->primaryKey,$this->associativePivots))
                unset($item->id);

            if(!empty($this->associativeColumns)) {

                $newItem = new \stdClass();

                foreach ($this->associativeColumns as $baseColumn => $mergeColumn) {
                    $newItem->$baseColumn = $item->$mergeColumn;
                }

                $item = $newItem;
            }


            $this->rowUpdatedByMatched += $query->update(get_object_vars($item));
        }

    }

    /**
     * Insert new elements into base table.
     *
     */
    private function insertUnMatched()
    {
        $newElements = $this->unMatchedCollection->map(function ($item) {

            //Unset primary key property because it can create Integrity constraint violation: Duplicate ID
            if(!in_array($this->primaryKey,$this->associativeColumns) && !in_array($this->primaryKey,$this->associativePivots))
                unset($item->id);

            if(!empty($this->associativeColumns))
            {
                $newItem = new \stdClass();
                foreach ($this->associativeColumns as $baseColumn => $mergeColumn)
                {
                    $newItem->$baseColumn = $item->$mergeColumn;
                }
                foreach ($this->associativePivots as $baseColumn => $mergeColumn)
                {
                    $newItem->$baseColumn = $item->$mergeColumn;
                }

                $item = $newItem;
            }


            return get_object_vars($item);

        })->toArray();

        if (!empty($newElements))
            $this->rowInserted = DB::table($this->baseTable)->insert($newElements);
    }

    /**
     * Start the reporting process
     */
    private function initReport()
    {
        $this->report = new \stdClass();

        $this->searchDiffs();
    }

    /**
     * Start looking for differences
     *
     */
    private function searchDiffs()
    {
        $this->matchedCollection->each(function ($item) {

            $newElement = $this->findMeIn($this->mergeCollection,$item);

            //Unset primary key property if it's not in associative pivots because it can create Integrity constraint violation: Duplicate ID
            if(!in_array($this->primaryKey,$this->associativeColumns) && !in_array($this->primaryKey,$this->associativePivots))
                unset($newElement->id);

            //If found element
            if($newElement)
                $this->fillReport($newElement,$item);
        });
    }

    /**
     * get an item from a passed collection where match with the passed item.
     * All based on the associative pivot list.
     * @param $collection
     * @param $search
     * @return bool
     */
    private function findMeIn($collection, $search)
    {
        foreach ($collection as $key => $item)
        {
            $found = true;
            foreach ($this->associativePivots as $basePivot => $mergePivot)
            {
                if($search->$basePivot != $item->$mergePivot)
                {
                    $found = false;
                    continue 2;
                }
            }

            if($found){
                return $item;
            }

        }

        return false;
    }

    /**
     * Fill Report Property.
     *
     * @param $newElement
     * @param $oldElement
     */
    private function fillReport($newElement,$oldElement)
    {

        if($newElement !== $oldElement){

            // It will search by column name matching
            if(empty($this->associativeColumns))
                $this->allColumns($newElement,$oldElement);
            else
                $this->byAssociativeColumns($newElement,$oldElement);
        }
    }

    /**
     * Get value from base table column and value from merge table column
     * Based on the associative array with columns.
     *
     * @param $newElement
     * @param $oldElement
     */
    private function byAssociativeColumns($newElement, $oldElement)
    {

        foreach ($this->associativeColumns as $baseColumn => $mergeColumn){

            if(!property_exists($oldElement,$baseColumn)){


                $this->report->$baseColumn[] = ['Column does not exist' => $newElement->$mergeColumn];

                continue;
            }

            if($newElement->$mergeColumn != $oldElement->$baseColumn){

                //Check this because it overwrite the before value. so it store only the last one.
                $this->report->$baseColumn[] = [$oldElement->$baseColumn => $newElement->$mergeColumn];
            }

        }
    }

    /**
     * Based on column name matching
     *
     * @param $newElement
     * @param $oldElement
     */
    private function allColumns($newElement, $oldElement)
    {

        foreach (get_object_vars($newElement) as $key => $newValue){

            if(!property_exists($oldElement,$key)){

                $this->report->$key[] = ['Column does not exist' => $newValue];

                continue;
            }

            if($newValue != $oldElement->$key){

                $this->report->$key[] = [$oldElement->$key => $newValue];

            }

        }
    }

    /**
     * create unmatched collection based on pivots
     *
     */
    private function loadUnMatchedCollection()
    {
        $this->unMatchedCollection = collect();

        foreach ($this->mergeCollection as $key => $item)
        {

            foreach ($this->associativePivots as $basePivot => $mergePivot)
            {
                if(!$this->baseCollection->whereIn($basePivot,$item->$mergePivot)->first())
                {
                    $this->unMatchedCollection->push($item);
                    continue 2;
                }
            }


        }

    }

    /**
     * Create matched collection based on pivots.
     */
    private function loadMatchedCollection()
    {
        $this->matchedCollection = collect();

        foreach ($this->baseCollection as $key => $item)
        {

            $found = true;
            foreach ($this->associativePivots as $basePivot => $mergePivot)
            {
                if(!$this->mergeCollection->whereIn($mergePivot,$item->$basePivot)->first())
                {
                    $found = false;
                    continue 2;
                }
            }

            if($found){
                $this->matchedCollection->push($item);
            }

        }
    }

    /**
     *Load Collections.
     */
    private function loadCollections()
    {
        $this->mergeCollection = $this->loadCollection($this->mergeTable);
        $this->baseCollection = $this->joinWhereIn(DB::table($this->baseTable))->get();

    }


    /**
     * Add whereIn closures to the DB Query based on associative pivots.
     *
     * @param $query
     * @return mixed
     */
    private function joinWhereIn($query)
    {
        foreach ($this->associativePivots as $basePivot => $mergePivot)
        {
            $query = $query->whereIn($basePivot,$this->mergeCollection->pluck($mergePivot)->toArray());
        }

        return $query;
    }

    /**
     * Get collection from a passed table name.
     *
     * @param $tableName
     * @return mixed
     */
    private function loadCollection($tableName)
    {
        return  DB::table($tableName)->get();
    }

    /**
     * @param $array
     */
    private function setClassTableProperties($array)
    {
        $this->tables = $array;

        $this->baseTable = $array[0];
        $this->mergeTable = $array[1];
    }


    /**
     * Validate the passed names.
     *
     * @param $names
     */
    private function validateTables($names)
    {
        foreach ($names as $name){
            $this->tableExist($name);
        }

    }

    /**
     * @param $columns
     */
    private function validateAssociativeColumns($columns)
    {
        foreach ($columns as $baseColumn => $mergeColumn)
        {
            //$this->existsInTable($this->baseTable,$baseColumn);
            $this->existsInTable($this->mergeTable,$mergeColumn);
        }

    }

    /**
     * Confirm pivot name exists in tables columns
     *
     * @param $table
     * @param $pivot
     * @return bool
     * @throws \Exception
     */
    private function existsInTable($table,$pivot)
    {
        if(!Schema::hasColumn($table,$pivot))
            throw new \Exception('Column not Found.');

        return true;

    }

    /**
     * Check if the table exists in the schema
     * If no exists, throw an exception.
     *
     * @param $name
     * @return $this
     * @throws \Exception
     */
    private function tableExist($name)
    {
        if(!Schema::hasTable($name))
            throw new \Exception('Table not found.');
    }


    //
    //MAGIC METHODS
    //

    /**
     * Magic access
     *
     * @param $name
     * @return mixed
     */
    function __get($name)
    {
        if(property_exists($this,$name))
            return $this->$name;

        return false;
    }



}