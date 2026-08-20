<?php
namespace Newsletter\Models;

use CodeIgniter\Model;

class SubscriberModel extends Model
{
    protected $table = 'newsletter_subscribers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'email', 'name', 'status', 'source', 'consent_text', 'consent_ip',
        'consented_at', 'unsubscribe_token', 'meta', 'email_domain',
        'quality_status', 'quality_score', 'quality_reasons', 'suppressed_at',
        'reviewed_at',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'email' => 'required|valid_email|max_length[255]|is_unique[newsletter_subscribers.email,id,{id}]',
        'status' => 'in_list[subscribed,unsubscribed,pending,bounced,suppressed]',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    public function createToken(): string
    {
        return bin2hex(random_bytes(24));
    }

    public function activeForList(?int $listId = null): array
    {
        $builder = $this->select('newsletter_subscribers.*')
            ->where('newsletter_subscribers.status', 'subscribed')
            ->groupStart()
                ->where('newsletter_subscribers.quality_status', 'valid')
                ->orWhere('newsletter_subscribers.quality_status IS NULL', null, false)
                ->orWhere('newsletter_subscribers.quality_status', 'unreviewed')
            ->groupEnd()
            ->where('newsletter_subscribers.suppressed_at', null);

        if ($listId) {
            $builder->join('newsletter_subscriber_lists', 'newsletter_subscriber_lists.subscriber_id = newsletter_subscribers.id')
                ->where('newsletter_subscriber_lists.list_id', $listId);
        }

        return $builder->findAll();
    }
}
