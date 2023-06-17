<?php

namespace kalanis\kw_groups\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces;
use kalanis\kw_groups\GroupsException;
use kalanis\kw_groups\Interfaces\ISource;
use kalanis\kw_locks\LockException;


/**
 * Class KwAuth
 * @package kalanis\kw_groups\Sources
 * Process the menu against the file tree
 * Load more already unloaded entries and remove non-existing ones
 */
class KwAuth implements ISource
{
    /** @var Interfaces\IAccessGroups */
    protected $lib = null;

    public function __construct(Interfaces\IAccessGroups $lib)
    {
        $this->lib = $lib;
    }

    public function get(): array
    {
        try {
            $groups = $this->lib->readGroup();
            /** @var array<string, array<int, string>> $result */
            $result = [];
            foreach ($groups as $group) {
                $result[$group->getGroupId()] = $group->getGroupParents();
            }
            return $result;
        } catch (AuthException | LockException $ex) {
            throw new GroupsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function create(Interfaces\IGroup $group): bool
    {
        try {
            $this->lib->createGroup($group);
            return true;
        } catch (AuthException | LockException $ex) {
            throw new GroupsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function read(string $groupId): ?Interfaces\IGroup
    {
        try {
            return $this->lib->getGroupDataOnly($groupId);
        } catch (AuthException | LockException $ex) {
            throw new GroupsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function update(Interfaces\IGroup $group): bool
    {
        try {
            return $this->lib->updateGroup($group);
        } catch (AuthException | LockException $ex) {
            throw new GroupsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function delete(string $groupId): bool
    {
        try {
            return $this->lib->deleteGroup($groupId);
        } catch (AuthException | LockException $ex) {
            throw new GroupsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}
