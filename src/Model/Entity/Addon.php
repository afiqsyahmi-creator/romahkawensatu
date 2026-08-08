<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Addon extends Entity
{
    protected array $_accessible = [
        'addon_name' => true,
        'addon_type' => true,
        'price' => true,
        'description' => true,
        'status' => true,
        'selection_type' => true,
        'is_popular' => true,
        'max_per_booking' => true,
        'weekly_capacity' => true,
        'weekly_booked' => true,
    ];
}
