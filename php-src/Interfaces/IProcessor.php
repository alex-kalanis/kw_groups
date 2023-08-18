<?php

namespace kalanis\kw_groups\Interfaces;


use kalanis\kw_groups\GroupsException;


/**
 * Interface IProcessor
 * @package kalanis\kw_groups\Interfaces
 * Interface which say if that group member can access that content
 */
interface IProcessor
{
    /**
     * Can my group access things with wanted group?
     * @param string $myGroup
     * @param string $wantedGroup
     * @throws GroupsException
     * @return bool
     */
    public function canAccess(string $myGroup, string $wantedGroup): bool;
}
