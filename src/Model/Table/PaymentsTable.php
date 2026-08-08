<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class PaymentsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('payment');
        $this->setPrimaryKey('payment_id');
        $this->setDisplayField('payment_id');
        $this->addBehavior('Timestamp', [
            'events' => ['Model.beforeSave' => ['payment_date' => 'new']],
        ]);
        $this->belongsTo('Bookings', ['foreignKey' => 'booking_id']);
    }
}
