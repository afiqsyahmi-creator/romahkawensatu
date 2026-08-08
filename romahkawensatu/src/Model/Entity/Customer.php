<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Customer extends Entity
{
    protected array $_accessible = [
        'customer_name' => true,
        'phone_number' => true,
        'email' => true,
        'bookings' => true,
    ];
}
