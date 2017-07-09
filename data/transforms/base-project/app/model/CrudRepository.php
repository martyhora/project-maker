<?php

namespace App\Model;

class CrudRepository extends BaseRepository
{	   
    protected $tableName = 'crud';

    protected $filterColumns = [
        'like'  => [/** @likeFilterColumns */],
        'equal' => [/** @equalFilterColumns */],
    ];
}
