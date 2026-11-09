<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function getAllWithProfiles(?string $role = null): Collection;
    public function findById(int $id): ?User;
    public function findByUsernameOrEmail(string $identifier): ?User;
    public function create(array $data): User;
    public function update(int $id, array $data): User;
    public function delete(int $id): bool;
}
