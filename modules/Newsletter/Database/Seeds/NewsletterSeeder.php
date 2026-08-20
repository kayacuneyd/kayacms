<?php
namespace Newsletter\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NewsletterSeeder extends Seeder
{
    public function run()
    {
        $exists = $this->db->table('newsletter_lists')->where('slug', 'main')->countAllResults();
        if ((int) $exists === 0) {
            $this->db->table('newsletter_lists')->insert([
                'name' => 'Main Newsletter',
                'slug' => 'main',
                'description' => 'Default site newsletter list.',
                'is_default' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
