<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CustomersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('customer');
        $this->setPrimaryKey('customer_id');
        $this->setDisplayField('customer_name');
        $this->addBehavior('Timestamp');
        $this->hasMany('Bookings', ['foreignKey' => 'customer_id']);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('customer_name')
            ->notEmptyString('phone_number')
            ->allowEmptyString('email')
            ->email('email', false);
        return $validator;
    }
}
