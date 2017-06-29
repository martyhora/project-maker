<?php

namespace App\Model;

class UserRepository extends BaseRepository
{
    protected $tableName = 'user';

    /**
     * @return \Nette\Database\Table\ActiveRow
     */
    public function findByName($username)
    {
        return $this->findBy(['username' => $username])->fetch();
    }
}
