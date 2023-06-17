<?php

namespace kalanis\kw_groups\Processor;


use kalanis\kw_auth\Interfaces\IGroup;
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
    /** @var ISource */
    protected $source = null;
    /** @var array<string, array<int, string>> */
    protected $cachedTree = [];
    /** @var array<string, bool> */
    protected $cachedThrough = [];

    public function __construct(ISource $source)
    {
        $this->source = $source;
    }

    public function canAccess(string $myGroup, string $wantedGroup): bool
    {
        $this->cachedThrough = [];
        return $this->represents($wantedGroup, $myGroup);
    }

    public function create(IGroup $group): bool
    {
        $this->cachedThrough = [];
        if ($this->source->read($group->getGroupId())) {
            // already exists
            return false;
        }
        if ($this->alreadyInParents($group)) {
            return false;
        }
        $result = $this->source->create($group);
        $this->dropCache();
        return $result;
    }

    public function read(string $groupId): ?IGroup
    {
        return $this->source->read($groupId);
    }

    public function update(IGroup $group): bool
    {
        $this->cachedThrough = [];
        if ($this->alreadyInParents($group)) {
            return false;
        }
        $result = $this->source->update($group);
        $this->dropCache();
        return $result;
    }

    public function delete(string $groupId): bool
    {
        if ($this->isChildSomewhere($groupId)) {
            return false;
        }
        $result = $this->source->delete($groupId);
        $this->dropCache();
        return $result;
    }

    /**
     * @param IGroup $group
     * @throws GroupsException
     * @return bool
     */
    protected function alreadyInParents(IGroup $group): bool
    {
        foreach ($group->getGroupParents() as $groupParent) {
            if ($this->represents($group->getGroupId(), $groupParent)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $groupId
     * @throws GroupsException
     * @return bool
     */
    protected function isChildSomewhere(string $groupId): bool
    {
        foreach ($this->cachedTree() as $tree) {
            if (in_array($groupId, $tree)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $parentGroup is current one and is stable through all the time during questioning
     * @param string $wantedGroup is to compare and changing as is changed processed branch
     * @throws GroupsException
     * @return bool
     */
    protected function represents(string $parentGroup, string $wantedGroup): bool
    {
        if ($parentGroup == $wantedGroup) {
            // it's me!
            return true;
        }

        $groups = $this->cachedTree();
        if (!isset($groups[$wantedGroup])) {
            // that group id does not exists in tree
            return false;
        }

        foreach ($groups[$wantedGroup] as $represents) {
            // against cyclic dependence - manually added groups
            if (isset($this->cachedThrough[$represents])) {
                return false;
            }
            $this->cachedThrough[$represents] = true;

            // somewhere in sub-groups
            if ($this->represents($parentGroup, $represents)) {
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
