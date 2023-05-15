<?php

namespace kalanis\kw_groups\Interfaces;


/**
 * Interface IProcessor
 * @package kalanis\kw_groups\Interfaces
 * Library which say if that group member can access that content
 * Based on sources
 */
interface IProcessor
{
    /**
     * Can my group access things with wanted group?
     * @param string $myGroup
     * @param string $wantedGroup
     * @return bool
     */
    public function canAccess(string $myGroup, string $wantedGroup): bool;
}
