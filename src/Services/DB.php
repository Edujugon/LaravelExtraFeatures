<?php
/**
 * Project: LaravelExtraFeatures.
 * User: Edujugon
 * Email: edujugon@gmail.com
 * Date: 14/2/17
 * Time: 12:34
 */

namespace Edujugon\LaravelExtraFeatures\Services;

use \Illuminate\Support\Facades\DB as IlluminateDB;

class DB
{
    public function getPrimaryKey($table,$schema = null)
    {

        if(!$schema){
            if(function_exists('env'))
            {
                $schema = env('DB_DATABASE');
            }else{
                throw new \Edujugon\LaravelExtraFeatures\Exceptions\DB('You have to pass a schema name into "getPrimaryKey" method or add DB_DATABASE key in .env file');
            }
        }

        $query = IlluminateDB::raw("SELECT k.column_name FROM information_schema.table_constraints t ".
            "JOIN information_schema.key_column_usage k " .
            "USING(constraint_name,table_schema,table_name) " .
            "WHERE t.constraint_type='PRIMARY KEY' ".
            "AND t.table_schema='". $schema ."'".
            "AND t.table_name='$table' "
        );

        return IlluminateDB::select($query)[0]->column_name;
    }
}