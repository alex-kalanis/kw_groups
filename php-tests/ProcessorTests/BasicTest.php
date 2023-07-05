<?php

namespace ProcessorTests;


use kalanis\kw_auth_sources\Data\FileGroup;
use kalanis\kw_auth_sources\Interfaces\IGroup;
use kalanis\kw_groups\GroupsException;
use kalanis\kw_groups\Interfaces\ISource;
use kalanis\kw_groups\Processor\Basic;


class BasicTest extends \CommonTestClass
{
    /**
     * @param string $my
     * @param string $want
     * @param bool $result
     * @throws GroupsException
     * @dataProvider accessProvider
     */
    public function testAccess(string $my, string $want, bool $result): void
    {
        $lib = new Basic(new XSource());
        $this->assertEquals($result, $lib->canAccess($my, $want));
    }

    public function accessProvider(): array
    {
        return [
            ['3', '3', true], // mine
            ['5', '4', true], // I am group 5, also represents parent 4
            ['5', '1', true], // I am group 5, also represents grandparent 1
            ['1', 'nop', false], // not exists parent
            ['nop', '1', false], // not exists me
            ['93', '4', false], // cyclic one - bad source data
        ];
    }

    /**
     * @throws GroupsException
     */
    public function testCreate(): void
    {
        $grp = new FileGroup();
        $grp->setGroupData('6', 'other', 'other', '2', 9, ['3']);

        $lib = new Basic(new XSource());
        // new
        $this->assertTrue($lib->create($grp));
        // already exists
        $this->assertFalse($lib->create($grp));
    }

    /**
     * @throws GroupsException
     */
    public function testCreateInParents(): void
    {
        $grp = new FileGroup();
        $grp->setGroupData('11', 'testing', 'testing', '2', 9, ['5']);

        $lib = new Basic(new XSource());
        $this->assertFalse($lib->create($grp));
    }

    /**
     * @throws GroupsException
     */
    public function testRead(): void
    {
        $lib = new Basic(new XSource());
        $this->assertNull($lib->read('123456'));

        $group = $lib->read('5');
        $this->assertEquals('5', $group->getGroupId());
        $this->assertEquals('extra', $group->getGroupName());
        $this->assertEquals('extra', $group->getGroupDesc());
        $this->assertEquals('1', $group->getGroupAuthorId());
        $this->assertEquals(1, $group->getGroupStatus());
        $this->assertEquals(['2', '4', '11'], $group->getGroupParents());
    }

    /**
     * @throws GroupsException
     */
    public function testUpdate(): void
    {
        $grp = new FileGroup();
        $grp->setGroupData('4', 'sys', 'sys', '2', 2, ['2']);

        $lib = new Basic(new XSource());

        $contains = $lib->read('4');
        $this->assertNotEmpty($contains);
        $this->assertEquals(['1'], $contains->getGroupParents());

        $this->assertTrue($lib->update($grp));

        $contains = $lib->read('4');
        $this->assertNotEmpty($contains);
        $this->assertEquals(['2'], $contains->getGroupParents());
    }

    /**
     * @throws GroupsException
     */
    public function testUpdateInParents(): void
    {
        $grp = new FileGroup();
        $grp->setGroupData('2', 'testing', 'testing', '2', 9, ['5']);

        $lib = new Basic(new XSource());
        $this->assertFalse($lib->update($grp));
    }

    /**
     * @throws GroupsException
     */
    public function testDelete(): void
    {
        $lib = new Basic(new XSource());
        // first ok
        $this->assertTrue($lib->delete('5'));
        // second not - already unknown
        $this->assertFalse($lib->delete('5'));
    }

    /**
     * @throws GroupsException
     */
    public function testDeleteWithChild(): void
    {
        $lib = new Basic(new XSource());
        $this->assertFalse($lib->delete('4'));
    }
}


/**
 * Class XSource
 * @package ProcessorTests
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
class XSource implements ISource
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
        $grp5->setGroupData('5', 'extra', 'extra', '1', 1, ['2', '4', '11']);
        $this->internal[] = $grp5;

        // cyclic dependencies - to failure
        $grp5 = new FileGroup();
        $grp5->setGroupData('91', 'test1', 'test1', '1', 9, ['1', '92']);
        $this->internal[] = $grp5;

        $grp5 = new FileGroup();
        $grp5->setGroupData('92', 'test2', 'test2', '2', 9, ['3', '91']);
        $this->internal[] = $grp5;

        $grp5 = new FileGroup();
        $grp5->setGroupData('93', 'test3', 'test3', '2', 9, ['92']);
        $this->internal[] = $grp5;
    }

    public function get(): array
    {
        $items = [];
        foreach ($this->internal as $item) {
            $items[$item->getGroupId()] = $item->getGroupParents();
        }
        return $items;
    }

    public function create(IGroup $group): bool
    {
        foreach ($this->internal as $item) {
            if ($group->getGroupId() == $item->getGroupId()) {
                return false;
            }
        }
        $this->internal[] = $group;
        return true;
    }

    public function read(string $groupId): ?IGroup
    {
        foreach ($this->internal as $item) {
            if ($groupId == $item->getGroupId()) {
                return $item;
            }
        }
        return null;
    }

    public function update(IGroup $group): bool
    {
        foreach ($this->internal as $key => $item) {
            if ($group->getGroupId() == $item->getGroupId()) {
                $this->internal[$key] = $group;
                return true;
            }
        }
        return false;
    }

    public function delete(string $groupId): bool
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
