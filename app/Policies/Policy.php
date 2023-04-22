<?php

namespace App\Policies;

use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Tobuli\Entities\User;

class Policy
{
    use HandlesAuthorization;

    protected $permisionKey = null;

    public function view(User $user, Model $entity = null)
    {
        if ($user->isAdmin())
            return true;

        if ($this->permisionKey && ! $user->perm($this->permisionKey, 'view'))
            return false;

        return true;
    }

    public function create(User $user, Model $entity = null)
    {
        if ($user->isDemo())
            return true;

        return $this->store($user, $entity);
    }

    public function store(User $user, Model $entity = null)
    {
        if ($user->isDemo())
            return false;

        if ($user->isAdmin())
            return true;

        if ($this->permisionKey && ! $user->perm($this->permisionKey, 'edit'))
            return false;

        return true;
    }

    public function show(User $user, Model $entity)
    {
        if ($user->isAdmin())
            return true;

        if ($this->permisionKey && ! $user->perm($this->permisionKey, 'view'))
            return false;

        return $this->ownership($user, $entity);
    }

    public function edit(User $user, Model $entity = null)
    {
        if ($user->isDemo())
            return true;

        return $this->update($user, $entity);
    }

    public function update(User $user, Model $entity)
    {
        if ($user->isDemo())
            return false;

        if ($user->isAdmin())
            return true;

        if ($this->permisionKey && ! $user->perm($this->permisionKey, 'edit'))
            return false;

        return $this->ownership($user, $entity);
    }

    public function remove(User $user, Model $entity = null)
    {
        return $this->destroy($user, $entity);
    }

    public function destroy(User $user, Model $entity = null)
    {
        return $this->clean($user, $entity) && $this->ownership($user, $entity);
    }

    public function clean(User $user, Model $entity = null)
    {
        if ($user->isAdmin())
            return true;

        if ($this->permisionKey && ! $user->perm($this->permisionKey, 'remove'))
            return false;

        return true;
    }

    public function active(User $user, Model $entity)
    {
        if ($user->isDemo())
            return false;

        return $this->view($user, $entity);
    }

    public function own(User $user, Model $entity) {
        return $this->ownership($user, $entity);
    }

    protected function ownership(User $user, Model $entity)
    {
        if (method_exists($entity, 'users') && $entity->users() instanceof BelongsToMany)
            return $this->ownershipMany($user, $entity);

        if (method_exists($entity, 'users') && $entity->users() instanceof HasMany)
            return $this->ownershipMany($user, $entity);

        if (method_exists($entity, 'user') && $entity->user() instanceof BelongsTo)
            return $this->ownershipOne($user, $entity);

        if (method_exists($entity, 'user') && $entity->user() instanceof HasOne)
            return $this->ownershipOne($user, $entity);

        throw new Exception("Class '".get_class($entity)."' dont have User relations");
    }

    protected function ownershipOne(User $user, Model $entity)
    {
        if ( ! $entity->user)
            return false;

        if ($entity->user->id === $user->id)
            return true;

        if ($user->isManager() && $entity->user->manager_id === $user->id)
            return true;

        return false;
    }

    protected function ownershipMany(User $user, Model $entity)
    {
        if ( ! $entity->users)
            return false;

        if ($entity->users->contains($user->id))
            return true;

        if ($user->isManager() && $entity->users->where('manager_id', $user->id)->all())
            return true;

        return false;
    }
}
