<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Content\Libraries\CustomFields;
use Content\Models\ContentModel;
use Content\Models\ContentTypeSchemaModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class CustomFieldsTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('content')->where('1=1')->delete();
        $db->table('content_type_schemas')->where('1=1')->delete();

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'customuser',
            'email' => 'custom@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    private function schema(): array
    {
        return [
            ['name' => 'color', 'label' => 'Color', 'type' => 'select', 'options' => ['red', 'blue', 'green'], 'required' => true],
            ['name' => 'price', 'label' => 'Price', 'type' => 'number', 'default' => 10],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
            ['name' => 'featured_badge', 'label' => 'Badge', 'type' => 'toggle'],
        ];
    }

    public function testSchemaModelUpsertsAndReadsSchema(): void
    {
        $model = new ContentTypeSchemaModel();
        $id = $model->setSchema('product', $this->schema());

        $this->assertIsInt($id);
        $this->assertSame($this->schema(), $model->getSchema('product'));
        $this->assertNull($model->getSchema('article'));

        $model->setSchema('product', [['name' => 'sku', 'label' => 'SKU', 'type' => 'text']]);
        $this->assertSame('sku', $model->getSchema('product')[0]['name']);

        $rows = $model->allWithTypes();
        $this->assertCount(1, $rows);
        $this->assertArrayHasKey('fields', $rows[0]);
        $this->assertSame('product', $rows[0]['content_type']);
    }

    public function testCustomFieldsCollectAppliesDefaultsAndNormalizes(): void
    {
        $model = new ContentTypeSchemaModel();
        $model->setSchema('product', $this->schema());

        $custom = new CustomFields();
        $data = $custom->collect(['custom' => ['color' => 'red', 'notes' => 'Hello']], 'product');

        $this->assertSame('red', $data['color']);
        $this->assertSame(10.0, $data['price']);
        $this->assertSame('Hello', $data['notes']);
        $this->assertSame(0, $data['featured_badge']);
    }

    public function testCustomFieldsValidateCatchesRequiredAndInvalidSelect(): void
    {
        $model = new ContentTypeSchemaModel();
        $model->setSchema('product', $this->schema());

        $custom = new CustomFields();
        $errors = $custom->validate(['color' => '', 'price' => 5, 'notes' => 'x', 'featured_badge' => 0], 'product');

        $this->assertNotEmpty($errors);

        $ok = $custom->validate(['color' => 'blue', 'price' => 5, 'notes' => 'x', 'featured_badge' => 1], 'product');
        $this->assertSame([], $ok);
    }

    public function testAdminStoresSchemaViaEndpoint(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'custom@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $result = $this->post('/admin/content/schemas/store/product', [
            'fields' => [
                ['name' => 'color', 'label' => 'Color', 'type' => 'select', 'options' => "red\nblue", 'required' => '1'],
                ['name' => 'price', 'label' => 'Price', 'type' => 'number', 'default' => '5'],
            ],
        ]);

        $result->assertRedirectTo('/admin/content/schemas');

        $schema = (new ContentTypeSchemaModel())->getSchema('product');
        $this->assertSame('color', $schema[0]['name']);
        $this->assertSame(['red', 'blue'], $schema[0]['options']);
        $this->assertTrue($schema[0]['required']);
        $this->assertSame(5, $schema[1]['default']);
    }

    public function testSchemaAdminPagesRender(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'custom@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        (new ContentTypeSchemaModel())->setSchema('product', $this->schema());

        $index = $this->get('/admin/content/schemas');
        $index->assertOK();
        $index->assertSee('Custom Fields');
        $index->assertSee('product');

        $edit = $this->get('/admin/content/schemas/edit/product');
        $edit->assertOK();
        $edit->assertSee('Color');
        $edit->assertSee('field-type');

        $form = $this->get('/admin/content/create');
        $form->assertOK();
        $form->assertSee('custom-fields-block');
    }

    public function testContentStorePersistsCustomData(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'custom@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        (new ContentTypeSchemaModel())->setSchema('product', $this->schema());

        $this->post('/admin/content/store', [
            'title' => 'Widget One',
            'slug' => 'widget-one',
            'body' => '<p>Nice widget</p>',
            'content_type' => 'product',
            'status' => 'published',
            'custom' => ['color' => 'green', 'price' => '25', 'notes' => 'A note'],
        ]);

        $content = (new ContentModel())->where('slug', 'widget-one')->first();
        $this->assertNotNull($content);

        $customData = $content->custom_data;
        $this->assertIsArray($customData);
        $this->assertSame('green', $customData['color']);
        $this->assertSame(25, $customData['price']);
        $this->assertSame('A note', $customData['notes']);
        $this->assertSame(0, $customData['featured_badge']);
    }

    public function testContentStoreRejectsInvalidCustomData(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'custom@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        (new ContentTypeSchemaModel())->setSchema('product', $this->schema());

        $result = $this->post('/admin/content/store', [
            'title' => 'Bad Widget',
            'slug' => 'bad-widget',
            'body' => '<p>x</p>',
            'content_type' => 'product',
            'status' => 'published',
            'custom' => ['color' => 'purple', 'price' => '1', 'notes' => ''],
        ]);

        $result->assertSessionHas('error');
        $this->assertNull((new ContentModel())->where('slug', 'bad-widget')->first());
    }

    public function testContentUpdatePersistsCustomData(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'custom@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        (new ContentTypeSchemaModel())->setSchema('product', $this->schema());

        $model = new ContentModel();
        $id = $model->insert([
            'title' => 'Widget Two',
            'slug' => 'widget-two',
            'body' => '<p>x</p>',
            'content_type' => 'product',
            'status' => 'draft',
            'author_id' => (int) (new UserModel())->findByEmail('custom@kayacms.local')->id,
            'custom_data' => json_encode(['color' => 'blue', 'price' => 3.0, 'notes' => 'orig', 'featured_badge' => 1]),
        ]);

        $this->post("/admin/content/update/{$id}", [
            'title' => 'Widget Two Updated',
            'slug' => 'widget-two-v2',
            'body' => '<p>updated</p>',
            'content_type' => 'product',
            'status' => 'draft',
            'custom' => ['color' => 'red', 'price' => '9', 'notes' => '', 'featured_badge' => '0'],
        ]);

        $content = $model->find($id);
        $this->assertNotNull($content);
        $this->assertSame('red', $content->custom_data['color']);
        $this->assertSame(9, $content->custom_data['price']);
        $this->assertSame(0, $content->custom_data['featured_badge']);
    }

    public function testApiExposesCustomDataDecoded(): void
    {
        (new ContentTypeSchemaModel())->setSchema('product', $this->schema());

        $model = new ContentModel();
        $model->insert([
            'title' => 'Widget API',
            'slug' => 'widget-api',
            'body' => '<p>x</p>',
            'content_type' => 'product',
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
            'author_id' => (int) (new UserModel())->findByEmail('custom@kayacms.local')->id,
            'custom_data' => json_encode(['color' => 'green', 'price' => 42.5, 'notes' => 'api note']),
        ]);

        $result = $this->get('/api/content/widget-api');
        $result->assertOK();

        $data = json_decode((string) $result->getJSON(), true);
        $row = $data['data'] ?? $data;
        $this->assertSame('green', $row['custom_data']['color']);
        $this->assertSame(42.5, $row['custom_data']['price'] ?? null);
        $this->assertArrayHasKey('render', $row);
    }
}