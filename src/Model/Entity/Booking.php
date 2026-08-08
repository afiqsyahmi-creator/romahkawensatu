<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Booking extends Entity
{
    protected array $_accessible = [
        'customer_id' => true,
        'studio_id' => true,
        'booking_date' => true,
        'start_time' => true,
        'end_time' => true,
        'pax' => true,
        'event_type' => true,
        'notes' => true,
        'total_price' => true,
        'booking_status' => true,
        'customer' => true,
        'studio' => true,
        'payments' => true,
        'addons' => true,
    ];
}
