<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class BookingAddon extends Entity
{
    protected array $_accessible = [
        'booking_id' => true,
        'addon_id' => true,
        'quantity' => true,
        'price_at_booking' => true,
    ];
}
