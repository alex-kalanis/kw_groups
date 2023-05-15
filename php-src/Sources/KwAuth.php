<?php

namespace kalanis\kw_groups\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces;
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

    /**
     * @throws AuthException
     * @throws LockException
     * @return array<string, array<int, string>>
     */
    public function get(): array
    {
        $groups = $this->lib->readGroup();
        /** @var array<string, array<int, string>> $result */
        $result = [];
        foreach ($groups as $group) {
            $result[$group->getGroupId()] = $group->getGroupParents();
        }
        return $result;
    }

    /**
     * @param Interfaces\IGroup $group
     * @throws AuthException
     * @throws LockException
     * @return bool
     */
    public function update(Interfaces\IGroup $group): bool
    {
        return $this->lib->updateGroup($group);
    }
}
