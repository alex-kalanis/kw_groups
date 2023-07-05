<?php

namespace SourcesTests;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Data\FileGroup;
use kalanis\kw_auth_sources\Interfaces\IWorkGroups;
use kalanis\kw_auth_sources\Interfaces\IGroup;
use kalanis\kw_groups\GroupsException;
use kalanis\kw_groups\Sources\KwAuth;


class KwAuthTest extends \CommonTestClass
{
    /**
     * @throws GroupsException
     */
    public function testSimple(): void
    {
        $lib = new KwAuth(new XAccessGroups());
        $this->assertEquals([
            '1' => [],
            '2' => [],
            '3' => ['1'],
            '4' => ['1'],
            '5' => ['2', '4'],
        ], $lib->get());
    }

    /**
     * @throws GroupsException
     */
    public function testSimpleFail(): void
    {
        $lib = new KwAuth(new XFailedGroups());
        $this->expectException(GroupsException::class);
        $lib->get();
    }

    /**
     * @throws GroupsException
     */
    public function testCreate(): void
    {
        $grp = new FileGroup();
        $grp->setGroupData('6', 'other', 'other', '2', 9, ['3']);

        $lib = new KwAuth(new XAccessGroups());
        $this->assertTrue($lib->create($grp));
    }

    /**
     * @throws GroupsException
     */
    public function testCreateFail(): void
    {
        $lib = new KwAuth(new XFailedGroups());
        $this->expectException(GroupsException::class);
        $lib->create(new FileGroup());
    }

    /**
     * @throws GroupsException
     */
    public function testRead(): void
    {
        $lib = new KwAuth(new XAccessGroups());
        $this->assertNull($lib->read('123456'));

        $group = $lib->read('5');
        $this->assertEquals('5', $group->getGroupId());
        $this->assertEquals('extra', $group->getGroupName());
        $this->assertEquals('extra', $group->getGroupDesc());
        $this->assertEquals('1', $group->getGroupAuthorId());
        $this->assertEquals(1, $group->getGroupStatus());
        $this->assertEquals(['2', '4'], $group->getGroupParents());
    }

    /**
     * @throws GroupsException
     */
    public function testReadFail(): void
    {
        $lib = new KwAuth(new XFailedGroups());
        $this->expectException(GroupsException::class);
        $lib->read('anything');
    }

    /**
     * @throws GroupsException
     */
    public function testUpdate(): void
    {
        $grp = new FileGroup();
        $grp->setGroupData('4', 'sys', 'sys', '2', 2, ['2']);

        $lib = new KwAuth(new XAccessGroups());

        $contains = $lib->get();
        $this->assertEquals(['1'], $contains['4']);

        $this->assertTrue($lib->update($grp));

        $contains = $lib->get();
        $this->assertEquals(['2'], $contains['4']);
    }

    /**
     * @throws GroupsException
     */
    public function testUpdateFail(): void
    {
        $lib = new KwAuth(new XFailedGroups());
        $this->expectException(GroupsException::class);
        $lib->update(new FileGroup());
    }

    /**
     * @throws GroupsException
     */
    public function testDelete(): void
    {
        $lib = new KwAuth(new XAccessGroups());
        // first ok
        $this->assertTrue($lib->delete('4'));
        // second not - already unknown
        $this->assertFalse($lib->delete('4'));
    }

    /**
     * @throws GroupsException
     */
    public function testDeleteFail(): void
    {
        $lib = new KwAuth(new XFailedGroups());
        $this->expectException(GroupsException::class);
        $lib->delete('anything');
    }
}


/**
 * Class XAccessGroups
 * @package SourcesTests
 *
 * Basic group tree:
 *  |
 *  o--o----->  root
 *  |  o----->  base
 *  |  o----->  sys
 *  o--+----->  admin
 *     o--o-->  extra
 *
 * -> extra is in both admin and root group
 */
class XAccessGroups implements IWorkGroups
{
    /** @var IGroup[] */
    protected $internal = [];

    public function __construct()
    {
        $grp1 = new FileGroup();
        $grp1->setGroupData('1', 'root', 'root', '1', 1, []);
        $this->internal[] = $grp1;

        $grp2 = new FileGroup();
        $grp2->setGroupData('2', 'admin', 'admin', '1', 1, []);
        $this->internal[] = $grp2;

        $grp3 = new FileGroup();
        $grp3->setGroupData('3', 'base', 'under', '1', 1, ['1']);
        $this->internal[] = $grp3;

        $grp4 = new FileGroup();
        $grp4->setGroupData('4', 'sys', 'sys', '1', 1, ['1']);
        $this->internal[] = $grp4;

        $grp5 = new FileGroup();
        $grp5->setGroupData('5', 'extra', 'extra', '1', 1, ['2', '4']);
        $this->internal[] = $grp5;
    }

    public function createGroup(IGroup $group): bool
    {
        foreach ($this->internal as $item) {
            if ($group->getGroupId() == $item->getGroupId()) {
                return false;
            }
        }
        $this->internal[] = $group;
        return true;
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


class XFailedGroups implements IWorkGroups
{
    public function createGroup(IGroup $group): bool
    {
        throw new AuthSourcesException('mock');
    }

    public function getGroupDataOnly(string $groupId): ?IGroup
    {
        throw new AuthSourcesException('mock');
    }

    public function readGroup(): array
    {
        throw new AuthSourcesException('mock');
    }

    public function updateGroup(IGroup $group): bool
    {
        throw new AuthSourcesException('mock');
    }

    public function deleteGroup(string $groupId): bool
    {
        throw new AuthSourcesException('mock');
    }
}
