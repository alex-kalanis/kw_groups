<?php

namespace kalanis\kw_groups\Interfaces;


use kalanis\kw_auth\Interfaces\IGroup;

/**
 * Interface ISource
 * @package kalanis\kw_groups\Interfaces
 * Library which say if that group member can access that content
 */
interface ISource
{
    /**
     * Get structure from source
     * - groupId => array of parent ids
     * @return array<string, array<int, string>>
     */
    public function get(): array;

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
