<?php

namespace kalanis\kw_groups\Interfaces;


use kalanis\kw_auth\Interfaces\IGroup;


/**
 * Interface IActions
 * @package kalanis\kw_groups\Interfaces
 * Interface which say if that library can work with that content
 */
interface IActions
{
    /**
     * @param IGroup $group
     * @return bool
     */
    public function create(IGroup $group): bool;

    /**
     * @param string $groupId
     * @return IGroup|null
     */
    public function read(string $groupId): ?IGroup;

    /**
     * @param IGroup $group
     * @return bool
     */
    public function update(IGroup $group): bool;

    /**
     * @param string $groupId
     * @return bool
     */
    public function delete(string $groupId): bool;
}
