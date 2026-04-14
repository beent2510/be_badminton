<?php
namespace App\Repositories;
use App\Core\BasicRepository;
use App\Models\User;

class AuthRepository extends BasicRepository
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }
}