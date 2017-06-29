<?php

namespace App\Model;

class TransformRepository extends BaseRepository
{	   
    protected $tableName = 'transform';

    protected $filterColumns = [
        'like'  => ['title', 'date_created'],
        'equal' => [],
    ];
}
