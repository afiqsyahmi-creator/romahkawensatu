<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Payment extends Entity
{
    protected array $_accessible = [
        'booking_id' => true,
        'amount' => true,
        'payment_method' => true,
        'gateway_reference' => true,
        'receipt_path' => true,
        'payment_status' => true,
        'booking' => true,
    ];
}
