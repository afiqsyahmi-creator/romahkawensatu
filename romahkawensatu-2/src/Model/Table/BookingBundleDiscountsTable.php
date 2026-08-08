<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class BookingBundleDiscountsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('booking_bundle_discount');
        $this->setPrimaryKey('discount_id');

        $this->belongsTo('Bookings', ['foreignKey' => 'booking_id']);
        $this->belongsTo('BundleOffers', ['foreignKey' => 'bundle_id']);
    }
}
