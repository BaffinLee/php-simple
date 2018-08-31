<?php

class UserModel extends Model
{
    protected $table = 'user';

    public function getUserInfo ($username)
    {
        return $this->selectOne('id, name, email', array('name' => $username));
    }
}