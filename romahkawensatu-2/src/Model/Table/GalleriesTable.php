<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class GalleriesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('gallery');
        $this->setPrimaryKey('gallery_id');
        $this->setDisplayField('caption');
        $this->belongsTo('Studios', ['foreignKey' => 'studio_id']);
    }
}
