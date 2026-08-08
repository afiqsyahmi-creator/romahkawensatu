<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class StudiosTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('studio');
        $this->setPrimaryKey('studio_id');
        $this->setDisplayField('studio_name');
        $this->addBehavior('Timestamp');
        $this->hasMany('Bookings', ['foreignKey' => 'studio_id']);
        $this->hasMany('Galleries', ['foreignKey' => 'studio_id']);
    }
}
