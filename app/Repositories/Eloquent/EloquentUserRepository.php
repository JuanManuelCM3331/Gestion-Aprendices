<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function getAllWithProfiles(?string $role = null): Collection
    {
        $query = User::with(['instructorProfile.fichas', 'apprenticeProfile.ficha.trainingProgram']);

        if ($role) {
            $query->where('role', $role);
        }

        return $query->orderBy('role')->orderBy('name')->get();
    }

    public function findById(int $id): ?User
    {
        return User::with(['instructorProfile.fichas', 'apprenticeProfile.ficha.trainingProgram'])->find($id);
    }

    public function findByUsernameOrEmail(string $identifier): ?User
    {
        return User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user->fresh();
    }

    public function delete(int $id): bool
    {
        $user = User::findOrFail($id);
        return (bool) $user->delete();
    }
}
