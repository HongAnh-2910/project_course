<?php
namespace Modules\User\Repositories;

interface UserRepositoryInterface
{
    public function getAllUser($limit = 30);
}