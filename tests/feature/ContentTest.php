<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class ContentTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();
    }

    public function testFrontendHomepageLoads(): void
    {
        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('KayaCMS');
    }

    public function testAdminContentRequiresAuth(): void
    {
        $result = $this->get('/admin/content');
        $result->assertRedirectTo('/admin/login');
    }

    public function testSearchPageLoads(): void
    {
        $result = $this->get('/search?q=kayacms');
        $result->assertOK();
        $result->assertSee('Search');
    }
}
