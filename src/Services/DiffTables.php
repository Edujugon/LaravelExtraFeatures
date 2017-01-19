<?php
namespace App\Services;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * Collection of items that are in merge Table but no in base table
     * @var
     */
    protected $newItems;


    /**
     * stdClass object with the columns as properties.
     * each property will be an array with old and new values.
     *
     * @var
     */
    protected $report;

    /**
     * Table Column as a point of comparision for the tables.
     * @var string
     */
    protected $pivot='';


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

        $instance->setTableProperties([$baseTable,$mergeTable]);

        return $instance;
    }

    /**
     * Set pivot column.
     *
     * @param $name
     * @return $this
     */
    public function pivot($name)
    {
        $this->validatePivot($name);

        $this->pivot = $name;

        return $this;
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
        $this->findNewElementsBetweenCollections($this->pivot,$this->mergeCollection,$this->baseCollection);

        $this->getReport();

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
     * Do the value updates in the base table and insert new values.
     *
     * @return bool
     */
    public function merge(){

        $this->createNewColumnsInTable($this->baseTable,$this->mergeTable);

        $this->doMerge($this->baseTable,$this->pivot,$this->mergeCollection);

        return true;
    }

    /**
     * Get elements that are in the merge table but no in the base table.
     *
     *
     * @return mixed
     */
    public function getNewItems()
    {
        return $this->newItems;
    }

    //
    //PRIVATE METHODS
    //

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
     * @param $tableName
     * @param $mergeTableName
     */
    private function createNewColumnsInTable($tableName, $mergeTableName)
    {
        $columns = Schema::getColumnListing($mergeTableName);

        Schema::table($tableName, function (Blueprint $table) use ($columns) {

            foreach($columns as $key){
                if(!Schema::hasColumn($table->getTable(),$key)) {
                    $table->text($key)->nullable();
                }
            }
        });
    }

    /**
     * Insert and Update data to a table
     *
     * @param $tableName
     * @param $pivot
     * @param $collection
     */
    private function doMerge($tableName, $pivot, $collection)
    {
        $this->doMergeByPivotValue($tableName, $pivot, $collection);

        $this->doMergeForNewElements($tableName, $pivot);
    }

    /**
     * Update values based on the pivot table.
     *
     * @param $tableName
     * @param $pivot
     * @param $collection
     */
    private function doMergeByPivotValue($tableName, $pivot, $collection)
    {
        $equalItems = $this->removedNewItems($pivot, $collection, $this->newItems);

        $equalItems->each(function ($item) use ($tableName, $pivot, $collection) {

            //Unset id property because it can create Integrity constraint violation: Duplicate ID
            if ($pivot != 'id')
                unset($item->id);

            DB::table($tableName)->where($pivot, $item->$pivot)->update(get_object_vars($item));

        });
    }

    /**
     * Insert new elements into base table.
     *
     * @param $tableName
     * @param $pivot
     */
    private function doMergeForNewElements($tableName, $pivot)
    {
        $newElements = $this->newItems->map(function ($item) use ($pivot) {

            //Unset id property because it can create Integrity constraint violation: Duplicate ID
            if ($pivot != 'id')
                unset($item->id);

            return get_object_vars($item);

        })->toArray();

        if (!empty($newElements))
            DB::table($tableName)->insert($newElements);
    }

    /**
     * Start the reporting process
     */
    private function getReport()
    {
        $equalItems = $this->removedNewItems($this->pivot,$this->mergeCollection,$this->newItems);

        $this->searchDiffs($equalItems);

    }
    /**
     * Start looking for differences
     *
     * @param $mergeCollection
     */
    private function searchDiffs($mergeCollection)
    {
        $mergeCollection->each(function ($newElement) {

            //Unset id property because it can create Integrity constraint violation: Duplicate ID
            if($this->pivot != 'id'){
                unset($newElement->id);
            }

            $pivot = $this->pivot;
            $oldElement = $this->baseCollection->where($pivot,$newElement->$pivot)->first();

            //If found element
            if($oldElement)
                $this->fillReport($newElement,$oldElement);
        });
    }

    /**
     * Fill Report Property.
     *
     * @param $newElement
     * @param $oldElement
     */
    private function fillReport($newElement,$oldElement)
    {

        $this->report = new \stdClass();

        if($newElement !== $oldElement){

            foreach (get_object_vars($newElement) as $key => $newValue){

                if(!property_exists($oldElement,$key)){

                    $this->report->$key[] = ['Null' => $newValue];

                    continue;
                }

                if($newValue != $oldElement->$key){

                    $this->report->$key[] = [$oldElement->$key => $newValue];

                }

            };
        }
    }

    /**
     * Pull items that are new for the other collection.
     *
     * @param $pivot
     * @param $collection
     * @param $newCollection
     * @return mixed
     */
    private function removedNewItems($pivot, $collection, $newCollection)
    {

        if(!$newCollection->isEmpty())
            $collection = DB::table($this->mergeTable)->whereNotIn($pivot,$newCollection->pluck($pivot)->toArray())->get();

        return $collection;
    }

    /**
     * Find new elements between 2 collections.
     *
     * @param $pivot
     * @param $collection1
     * @param $collection2
     */
    private function findNewElementsBetweenCollections($pivot, $collection1, $collection2)
    {
        //Array of new items based on pivot table
        $newItems = $collection1->pluck($pivot)->diff($collection2->pluck($pivot));

        //Get real objects and assign to newItems property
        $this->newItems = DB::table($this->mergeTable)->whereIn($pivot,$newItems)->get();

    }

    /**
     *Load Collections.
     */
    private function loadCollections()
    {
        $this->loadCollection($this->mergeTable);
        $this->loadCollectionFromAnother($this->baseTable,$this->pivot,$this->mergeCollection);
    }

    /**
     * Load a collection based on another collection.
     * if some elements aren't in both then they aren't pushed.
     *
     * @param $tableName
     * @param $pivot
     * @param $mergeCollection
     */
    private function loadCollectionFromAnother($tableName, $pivot, $mergeCollection)
    {

        $this->baseCollection = DB::table($tableName)->whereIn($pivot,$mergeCollection->pluck($pivot)->toArray())->get();
    }

    /**
     * Get collection from a passed table name.
     *
     * @param $tableName
     * @return mixed
     */
    private function loadCollection($tableName)
    {
        $this->mergeCollection = DB::table($tableName)->get();
    }

    /**
     * @param $array
     */
    private function setTableProperties($array)
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
     * Validate passed name
     *
     * @param $name
     */
    private function validatePivot($name)
    {
        $this->existsInTables($name);
    }

    /**
     * Confirm pivot name exists in tables columns
     *
     * @param $pivot
     * @return bool
     * @throws \Exception
     */
    private function existsInTables($pivot)
    {
        foreach ($this->tables as $table)
        {
            if(!Schema::hasColumn($table,$pivot))
                throw new \Exception('Pivot Column not Found.');

        }

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