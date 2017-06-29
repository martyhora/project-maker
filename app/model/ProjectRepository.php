<?php

namespace App\Model;

class ProjectRepository extends BaseRepository
{	   
    protected $tableName = 'project';

    protected $filterColumns = [
        'like'  => ['title', 'date_created'],
        'equal' => ['transform_id'],
    ];
}
