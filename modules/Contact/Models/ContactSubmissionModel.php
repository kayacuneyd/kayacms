<?php

namespace Contact\Models;

use CodeIgniter\Model;

class ContactSubmissionModel extends Model
{
    protected $table = 'contact_submissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['contact_form_id', 'data', 'status', 'ip_address', 'user_agent', 'read_at'];
    protected $useTimestamps = true;

    public function getByForm(int $formId, string $status = ''): array
    {
        $builder = $this->where('contact_form_id', $formId);
        if ($status !== '') {
            $builder->where('status', $status);
        }
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function decodeData(array $submission): array
    {
        $submission['data'] = json_decode($submission['data'] ?? '{}', true);
        return $submission;
    }
}
