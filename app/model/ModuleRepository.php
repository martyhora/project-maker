<?php

namespace App\Model;

class ModuleRepository extends BaseRepository
{	   
    protected $tableName = 'module';

    protected $filterColumns = [
        'like'  => ['name', 'title', 'date_created'],
        'equal' => ['project_id'],
    ];
}
