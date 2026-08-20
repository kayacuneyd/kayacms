<?php
namespace Newsletter\Models;

use CodeIgniter\Model;

class CampaignModel extends Model
{
    protected $table = 'newsletter_campaigns';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'subject', 'preheader', 'body_html', 'body_text', 'status', 'provider',
        'list_id', 'scheduled_at', 'sent_at', 'created_by',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'subject' => 'required|min_length[3]|max_length[255]',
        'body_html' => 'required',
        'status' => 'in_list[draft,scheduled,sending,sent]',
        'provider' => 'in_list[smtp,external]',
    ];
}
