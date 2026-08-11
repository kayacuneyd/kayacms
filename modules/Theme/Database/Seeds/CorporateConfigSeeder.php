<?php

namespace Theme\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CorporateConfigSeeder extends Seeder
{
    public function run()
    {
        $config = [
            'brand_color' => '#de252a',
            'hero' => [
                ['headline' => 'Uzman Kadro', 'image' => 'https://kzhukuk.com/wp-content/uploads/2025/04/4.png', 'icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/expert.png', 'name' => 'Uzman Kadro', 'desc' => 'Alanında uzman avukatlarımızla, en karmaşık hukuki süreçlerde bile yanınızdayız.'],
                ['headline' => 'Güvenilir Danışmanlık', 'image' => 'https://kzhukuk.com/wp-content/uploads/2025/04/2.png', 'icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/trustworthy.png', 'name' => 'Güvenilir Danışmanlık', 'desc' => 'Müvekkillerimize dürüst, şeffaf ve güvenilir hukuki danışmanlık sunuyoruz.'],
                ['headline' => 'Hızlı ve Etkili Çözümler', 'image' => 'https://kzhukuk.com/wp-content/uploads/2025/04/3.png', 'icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/effective.png', 'name' => 'Hızlı ve Etkili Çözümler', 'desc' => 'Zamanın değerini biliyoruz. Etkili ve pratik çözümler sunarak süreçleri hızlandırıyoruz.'],
                ['headline' => 'Etik Değerler ve Gizlilik', 'image' => 'https://kzhukuk.com/wp-content/uploads/2025/04/hakkimizda-hero-dark.png', 'icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/ethical.png', 'name' => 'Etik Değerler ve Gizlilik', 'desc' => 'Müvekkil bilgileriniz en yüksek gizlilik ilkeleriyle korunur, her zaman etik değerlere sadığız.'],
            ],
            'intro_title' => 'Kaplan & Zorer Hukuk Bürosu',
            'intro_text' => "Kaplan & Zorer Hukuk Bürosu olarak, bireysel ve kurumsal müvekkillerimize ulusal ve uluslararası alanda hukuki danışmanlık hizmeti sunmaktayız. Hukukun üstünlüğünü esas alarak, adaletin sağlanması için şeffaf, hızlı ve etkili çözümler üretiyoruz.\n\nHukuki süreçlerinizi titizlikle yönetiyor, size en uygun stratejileri belirleyerek çözüm odaklı bir yaklaşım sunuyoruz.",
            'intro_image' => 'https://kzhukuk.com/wp-content/uploads/2025/04/hakkimizda-hero-dark.png',
            'vertical_text' => 'K&Z',
            'practice_title' => 'Çalışma Alanlarımız',
            'practice' => [
                ['icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/trade.png', 'title' => 'Ceza Hukuku', 'desc' => 'Ceza Hukuku kapsamında karşılaştığınız sorunlar alanında uzman kadromuzla titizlikle takip edilmektedir. Maddi ceza hukuku, ceza muhakemesi ve infaz hukukuna ilişkin meseleler uzmanlık alanımızdır.'],
                ['icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/family.png', 'title' => 'Tıp/Sağlık Hukuku', 'desc' => 'Sağlık hizmetlerinden kaynaklı eksik veya hatalı uygulama sorunları başta olmak üzere sağlık çalışanlarının ve hastaların hak ve yükümlülüklerine ilişkin sorunlar uzmanlık alanlarımız içerisindedir.'],
                ['icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/punishment.png', 'title' => 'Çevre Ceza Hukuku', 'desc' => 'Çevreye karşı gerçekleştirilen suçlar ve kabahatler alanında uzman kadromuz tarafından titizlikle takip edilmektedir. Çevrenin kirletilmesi, gürültüye neden olma ve imar kirliliğine neden olma suçları uzmanlık alanımızdır.'],
                ['icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/bankruptcy.png', 'title' => 'Ekonomi Ceza Hukuku', 'desc' => 'Ekonomik faaliyetlerin düzenlenmesinden kaynaklı ekonomi ceza hukukuna ilişkin sorunlarınızda uzman kadromuzla hizmetinizdeyiz.'],
                ['icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/real-estate.png', 'title' => 'Gayrimenkul Hukuku', 'desc' => 'İnşaat hukukuna ilişkin hukuki ve cezai sorunlar titizlikle takip edilmektedir. Sözleşme öncesi ve sonrası yükümlülükler, ruhsat, kat mülkiyeti ve imara aykırılıklara ilişkin sorunlarda etkin hizmet sunmaktayız.'],
                ['icon' => 'https://kzhukuk.com/wp-content/uploads/2025/04/employer.png', 'title' => 'Gümrük Hukuku', 'desc' => 'Gümrük Mevzuatı ile Kaçakçılıkla Mücadele Kanunu’ndan kaynaklı idari ve cezai uyuşmazlıklarınızda uzman kadromuzla yanınızdayız.'],
            ],
            'references_title' => 'Referanslarımız',
            'references' => [
                ['name' => 'Referans-1', 'quote' => 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores.'],
                ['name' => 'Referans-2', 'quote' => 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores.'],
                ['name' => 'Referans-3', 'quote' => 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores.'],
            ],
            'cta_kicker' => 'Size Nasıl Yardımcı Olabiliriz?',
            'cta_title' => 'Hukuki ihtiyaçlarınıza en uygun çözümler için hemen bizimle iletişime geçin.',
            'cta_phone' => 'Danışmanlık Talep Et',
            'cta_phone_url' => 'tel:+15551234567',
            'cta_btn_text' => 'Hemen Randevu Al',
            'cta_btn_url' => '/iletisim',
            'show_blog' => '1',
            'team_title' => 'Takımımız',
            'team' => [
                ['photo' => 'https://kzhukuk.com/wp-content/uploads/2025/04/mahmutkaplan.jpg', 'name' => 'Av. Doç. Dr. Mahmut Kaplan', 'email' => 'mkaplan@kzhukuk.com', 'linkedin' => 'https://linkedin.com/'],
                ['photo' => 'https://kzhukuk.com/wp-content/uploads/2025/04/zeynepzorerkaplan.jpg', 'name' => 'Av. Zeynep Zorer Kaplan, L.Lm', 'email' => 'zzorer@kzhukuk.com', 'linkedin' => 'https://linkedin.com/'],
                ['photo' => 'https://kzhukuk.com/wp-content/uploads/2025/04/cansugoekalp.jpg', 'name' => 'Av. Cansu Gökalp', 'email' => 'cansugokalp@kzhukuk.com', 'linkedin' => 'https://linkedin.com/'],
            ],
            'about_title' => 'Hakkımızda',
            'about' => [
                ['title' => 'Uzmanlık', 'desc' => 'Alanında deneyimli avukatlarımız, her dava türüne özel uzmanlıkla yaklaşır.'],
                ['title' => 'Güvenilirlik', 'desc' => 'Tüm süreçlerde müvekkillerimize karşı şeffaf, etik ve dürüst davranırız.'],
                ['title' => 'Erişilebilirlik', 'desc' => 'İletişimi ön planda tutar, her an ulaşılabilir ve yanıt verebilir olmaya özen gösteririz.'],
                ['title' => 'Çözüm Odaklılık', 'desc' => 'Sadece sorunları analiz etmekle kalmaz, etkili çözümler üretmeye odaklanırız.'],
            ],
            'footer_phone' => 'XXXXXXXXXX',
            'footer_fax' => 'XXXXXXXXXXX',
            'footer_address' => "Arapsuyu Mah. 621 Sok. No 1 Çetin Apt. K1 D3\n07070 Konyaaltı/Antalya",
            'footer_email' => '',
        ];

        $this->db->table('themes')->where('slug', 'corporate')->update([
            'config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        echo "Corporate config seeded.\n";
    }
}
