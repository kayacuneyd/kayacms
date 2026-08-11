<?php

namespace Feature;

use App\Libraries\Hooks;
use CodeIgniter\Test\CIUnitTestCase;

class HooksTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Hooks::reset();
    }

    public function testFilterTransformsValue(): void
    {
        Hooks::addFilter('content.title', static fn (string $title) => strtoupper($title));

        $result = Hooks::applyFilters('content.title', 'hello');

        $this->assertSame('HELLO', $result);
    }

    public function testFiltersRunInPriorityOrder(): void
    {
        $log = [];

        Hooks::addFilter('test.order', static function ($value) use (&$log) {
            $log[] = 'first';
            return $value . '-a';
        }, 5);

        Hooks::addFilter('test.order', static function ($value) use (&$log) {
            $log[] = 'default';
            return $value . '-b';
        }, 10);

        $result = Hooks::applyFilters('test.order', 'x');

        $this->assertSame('x-a-b', $result);
        $this->assertSame(['first', 'default'], $log);
    }

    public function testActionReceivesArguments(): void
    {
        $received = [];

        Hooks::addAction('content.saved', static function (int $id, string $title) use (&$received) {
            $received = [$id, $title];
        });

        Hooks::doAction('content.saved', 42, 'My Post');

        $this->assertSame([42, 'My Post'], $received);
    }

    public function testUnregisteredHooksAreNoOps(): void
    {
        $this->assertSame('unchanged', Hooks::applyFilters('missing.hook', 'unchanged'));
        Hooks::doAction('missing.action', 'ignored');
        $this->assertTrue(Hooks::applyFilters('missing.hook', 'unchanged') === 'unchanged');
    }

    public function testHasAndRegisteredHelpers(): void
    {
        Hooks::addFilter('a.hook', static fn ($v) => $v);
        Hooks::addAction('b.hook', static fn () => null);
        Hooks::addAction('b.hook', static fn () => null);

        $this->assertTrue(Hooks::hasFilter('a.hook'));
        $this->assertTrue(Hooks::hasAction('b.hook'));
        $this->assertFalse(Hooks::hasFilter('b.hook'));

        $registered = Hooks::registered();
        $this->assertSame(1, $registered['filter']['a.hook']);
        $this->assertSame(2, $registered['action']['b.hook']);
    }
}