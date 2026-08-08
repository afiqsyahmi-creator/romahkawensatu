<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Gallery extends Entity
{
    protected array $_accessible = [
        'studio_id' => true,
        'image_path' => true,
        'caption' => true,
        'sort_order' => true,
        'studio' => true,
    ];
}
