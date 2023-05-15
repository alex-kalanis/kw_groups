<?php

namespace SourcesTests;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Interfaces\IAccessGroups;
use kalanis\kw_auth\Interfaces\IGroup;
use kalanis\kw_groups\Sources\KwAuth;
use kalanis\kw_locks\LockException;


class KwAuthTest extends \CommonTestClass
{
    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testSimple(): void
    {
        $lib = new KwAuth(new XGroups());
        $this->assertEquals([
            '1' => [],
            '2' => [],
            '3' => ['1'],
            '4' => ['1'],
            '5' => ['2', '4'],
        ], $lib->get());
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testUpdate(): void
    {
        $grp = new FileGroup();
        $grp->setGroupData('4', 'sys', 'sys', '2', 2, ['2']);

        $lib = new KwAuth(new XGroups());
        $this->assertTrue($lib->update($grp));
    }
}


/**
 * Class XGroups
 * @package SourcesTests
 *
 * Basic group tree:
 *  |
 *  +-->        root
 *  |  +----->  base
 *  |  +----->  sys
 *  +----->     admin
 *     +--+-->  extra
 *
 * -> extra is in both admin and root group
 */
class XGroups implements IAccessGroups
{
    /** @var IGroup[] */
    protected $internal = [];

    public function __construct()
    {
        $grp1 = new FileGroup();
        $grp1->setGroupData(1, 'root', 'root', 1, 1, []);
        $this->internal[] = $grp1;

        $grp2 = new FileGroup();
        $grp2->setGroupData(2, 'admin', 'admin', 1, 1, []);
        $this->internal[] = $grp2;

        $grp3 = new FileGroup();
        $grp3->setGroupData(3, 'base', 'under', 1, 1, ['1']);
        $this->internal[] = $grp3;

        $grp4 = new FileGroup();
        $grp4->setGroupData(4, 'sys', 'sys', 1, 1, ['1']);
        $this->internal[] = $grp4;

        $grp5 = new FileGroup();
        $grp5->setGroupData(5, 'extra', 'extra', 1, 1, ['2', '4']);
        $this->internal[] = $grp5;
    }

    public function createGroup(IGroup $group): void
    {
        foreach ($this->internal as $item) {
            if ($group->getGroupId() == $item->getGroupId()) {
                return;
            }
        }
        $this->internal[] = $group;
    }

    public function getGroupDataOnly(string $groupId): ?IGroup
    {
        foreach ($this->internal as $item) {
            if ($groupId == $item->getGroupId()) {
                return $item;
            }
        }
        return null;
    }

    public function readGroup(): array
    {
        return $this->internal;
    }

    public function updateGroup(IGroup $group): bool
    {
        foreach ($this->internal as $key => $item) {
            if ($group->getGroupId() == $item->getGroupId()) {
                $this->internal[$key] = $group;
                return true;
            }
        }
        return false;
    }

    public function deleteGroup(string $groupId): bool
    {
        foreach ($this->internal as $key => $item) {
            if ($groupId == $item->getGroupId()) {
                unset($this->internal[$key]);
                return true;
            }
        }
        return false;
    }
}
