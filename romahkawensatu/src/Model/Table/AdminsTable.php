<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class AdminsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('admin');            // singular table name in your schema
        $this->setPrimaryKey('admin_id');
        $this->setDisplayField('full_name');
        $this->addBehavior('Timestamp');     // fills created_at
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->email('email')->notEmptyString('email')
            ->notEmptyString('username')
            ->notEmptyString('password_hash');
        return $validator;
    }
}
