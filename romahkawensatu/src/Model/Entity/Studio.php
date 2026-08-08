<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Studio extends Entity
{
    protected array $_accessible = [
        'studio_name' => true,
        'capacity' => true,
        'description' => true,
        'hourly_rate' => true,
        'image' => true,
        'status' => true,
        'galleries' => true,
        'bookings' => true,
    ];

    // Minimum spend = 2-hour rate. Used for "from RMxxx (2 hrs)" on cards.
    protected function _getMinPrice(): float
    {
        return (float)$this->hourly_rate * 2;
    }
}
