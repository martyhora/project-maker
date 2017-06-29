<?php

namespace App\Model;

class Crud extends BaseRepository
{	   
    protected $tableName = 'crud';

    protected $filterColumns = [
        'like'  => [/** @likeFilterColumns */],
        'equal' => [/** @equalFilterColumns */],
    ];
}
