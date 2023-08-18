<?php

namespace ProcessorTests;


use kalanis\kw_groups\GroupsException;
use kalanis\kw_groups\Processor\Basic;
use kalanis\kw_groups\Sources\SimpleArray;


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
        $lib = new Basic(new SimpleArray($this->sourceRelations()));
        $this->assertEquals($result, $lib->canAccess($my, $want));
        $lib->dropCache();
    }

    public function accessProvider(): array
    {
        return [
            ['3', '3', true], // mine
            ['5', '4', true], // I am group 5, also represents parent 4
            ['5', '1', true], // I am group 5, also represents grandparent 1
            ['4', '5', false], // I am group 4, but known nothing about 5
            ['1', 'nop', false], // not exists parent
            ['nop', '1', false], // not exists me
            ['93', '4', false], // cyclic one - bad source data
        ];
    }

    /**
     *  * Basic group tree:
     *  |
     *  o--o----->  1
     *  |  o----->  3
     *  |  o----->  4
     *  o--+----->  2
     *     o--o-->  5
     *
     * -> extra is in both admin and root group
     *
     * @return array<string, array<string>>
     */
    protected function sourceRelations(): array
    {
        return [
            '1' => [],
            '2' => [],
            '3' => ['1'],
            '4' => ['1'],
            '5' => ['2', '4', '11'],
            // cyclic dependencies - to failure
            '91' => ['1', '92'],
            '92' => ['3', '91'],
            '93' => ['92'],
        ];
    }
}
