<?php

namespace Unit;

use CodeIgniter\Test\CIUnitTestCase;

class ExampleTest extends CIUnitTestCase
{
    public function testEnvironmentIsTesting(): void
    {
        $this->assertSame('testing', ENVIRONMENT);
    }

    public function testBasicMath(): void
    {
        $this->assertSame(4, 2 + 2);
    }
}
