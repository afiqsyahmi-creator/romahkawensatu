<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class BundleOffer extends Entity
{
    protected array $_accessible = [
        'addon_id_1' => true,
        'addon_id_2' => true,
        'discount_amount' => true,
        'is_active' => true,
        'addon_1' => true,
        'addon_2' => true,
    ];
}
