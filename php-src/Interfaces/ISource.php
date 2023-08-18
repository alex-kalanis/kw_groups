<?php

namespace kalanis\kw_groups\Interfaces;


use kalanis\kw_groups\GroupsException;


/**
 * Interface ISource
 * @package kalanis\kw_groups\Interfaces
 * Interface which say if that group member can access that content
 */
interface ISource
{
    /**
     * Get structure from source
     * - groupId => array of parent ids
     * @throws GroupsException
     * @return array<string, array<int, string>>
     */
    public function get(): array;
}
