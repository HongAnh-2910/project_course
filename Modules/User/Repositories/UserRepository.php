<?php
namespace Modules\User\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function getModel()
    {
        return User::class;
    }

    public function getAllUser($limit = 30)
    {
        return $this->getAllPaginate($limit);
    }


}