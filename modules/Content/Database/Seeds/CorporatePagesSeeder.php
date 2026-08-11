<?php

namespace Content\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CorporatePagesSeeder extends Seeder
{
    public function run()
    {
        $pages = [
            [
                'slug' => 'hakkimizda',
                'title' => 'Hakkımızda',
                'handler' => 'template',
                'payload' => json_encode(['view' => 'themes/corporate/hakkimizda']),
            ],
            [
                'slug' => 'takim',
                'title' => 'Takım',
                'handler' => 'template',
                'payload' => json_encode(['view' => 'themes/corporate/takim']),
            ],
            [
                'slug' => 'calisma-alanlarimiz',
                'title' => 'Çalışma Alanlarımız',
                'handler' => 'template',
                'payload' => json_encode(['view' => 'themes/corporate/calisma-alanlarimiz']),
            ],
            [
                'slug' => 'iletisim',
                'title' => 'İletişim',
                'handler' => 'template',
                'payload' => json_encode(['view' => 'themes/corporate/virtual']),
            ],
        ];

        $db = $this->db->table('virtual_pages');

        foreach ($pages as $page) {
            if ($db->where('slug', $page['slug'])->countAllResults() === 0) {
                $db->insert(array_merge($page, [
                    'status' => 'active',
                ]));
            }
        }

        echo "Corporate virtual pages seeded.\n";
    }
}
