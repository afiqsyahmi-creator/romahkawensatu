<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class BookingBundleDiscount extends Entity
{
    protected array $_accessible = [
        'booking_id' => true,
        'bundle_id' => true,
        'discount_amount' => true,
        'bundle_offer' => true,
    ];
}
