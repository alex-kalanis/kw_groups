<?php

namespace kalanis\kw_groups\Sources;


use kalanis\kw_groups\Interfaces\ISource;


/**
 * Class SimpleArray
 * @package kalanis\kw_groups\Sources
 * Process the simple array as source of data to check with groups
 */
class SimpleArray implements ISource
{
    /** @var array<string, array<int, string>> */
    protected $content = [];

    /**
     * @param array<string, array<int, string>> $content
     */
    public function __construct(array $content)
    {
        $this->content = $content;
    }

    public function get(): array
    {
        return $this->content;
    }
}
