<?php

namespace kalanis\kw_groups\Processor;


use kalanis\kw_groups\GroupsException;
use kalanis\kw_groups\Interfaces\IProcessor;
use kalanis\kw_groups\Interfaces\ISource;


/**
 * Class Basic
 * @package kalanis\kw_groups\Processor
 * Basic processing of groups
 */
class Basic implements IProcessor
{
    protected ISource $source;
    /** @var array<string, array<int, string>> */
    protected array $cachedTree = [];
    /** @var array<string, bool> */
    protected array $cachedThrough = [];

    public function __construct(ISource $source)
    {
        $this->source = $source;
    }

    public function canAccess(string $myGroup, string $wantedGroup): bool
    {
        $this->cachedThrough = [];
        return $this->represents($wantedGroup, $myGroup);
    }

    /**
     * @param string $currentGroup is current one and is stable through all the time during questioning
     * @param string $wantedGroup is to compare and changing as is changed processed branch
     * @throws GroupsException
     * @return bool
     */
    protected function represents(string $currentGroup, string $wantedGroup): bool
    {
        if ($currentGroup == $wantedGroup) {
            // it's me!
            return true;
        }

        $groups = $this->cachedTree();
        if (!isset($groups[$wantedGroup])) {
            // that group id does not exists in tree
            return false;
        }

        foreach ($groups[$wantedGroup] as $group) {
            // against cyclic dependence - manually added groups
            if (isset($this->cachedThrough[$group])) {
                return false;
            }
            $this->cachedThrough[$group] = true;

            // somewhere in sub-groups
            if ($this->represents($currentGroup, $group)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws GroupsException
     * @return array<string, array<int, string>>
     */
    protected function cachedTree(): array
    {
        if (empty($this->cachedTree)) {
            $this->cachedTree = $this->source->get();
        }
        return $this->cachedTree;
    }

    public function dropCache(): self
    {
        $this->cachedTree = [];
        return $this;
    }
}
