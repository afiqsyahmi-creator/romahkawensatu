<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class BookingAddonsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('booking_addon');
        // composite primary key (booking_id, addon_id)
        $this->setPrimaryKey(['booking_id', 'addon_id']);
        $this->belongsTo('Bookings', ['foreignKey' => 'booking_id']);
        $this->belongsTo('Addons', ['foreignKey' => 'addon_id']);
    }
}
