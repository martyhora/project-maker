<?php

namespace App\Model;

class EstateRepository extends BaseRepository
{	   
    protected $tableName = 'estate';

    protected $filterColumns = [
        'like'  => ['title'],
        'equal' => ['type_id', 'disposition_id', 'broker_id'],
    ];
}
