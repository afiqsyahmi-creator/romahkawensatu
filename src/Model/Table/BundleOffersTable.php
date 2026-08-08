<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class BundleOffersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('bundle_offer');
        $this->setPrimaryKey('bundle_id');
        $this->setDisplayField('bundle_id');

        $this->belongsTo('Addon1', [
            'className' => 'Addons',
            'foreignKey' => 'addon_id_1',
        ]);
        $this->belongsTo('Addon2', [
            'className' => 'Addons',
            'foreignKey' => 'addon_id_2',
        ]);
    }

    /**
     * Find all active bundles, eager-loading the two add-ons.
     */
    public function findActive(): array
    {
        return $this->find()
            ->where(['is_active' => true])
            ->contain(['Addon1', 'Addon2'])
            ->orderBy(['bundle_id' => 'ASC'])
            ->all()
            ->toArray();
    }

    /**
     * For a given set of selected add-on IDs, return matching bundles.
     */
    public function findMatchingBundles(array $selectedAddonIds): array
    {
        if (count($selectedAddonIds) < 2) {
            return [];
        }
        $ids = array_map('intval', $selectedAddonIds);

        return $this->find()
            ->where([
                'is_active' => true,
                'addon_id_1 IN' => $ids,
                'addon_id_2 IN' => $ids,
            ])
            ->contain(['Addon1', 'Addon2'])
            ->all()
            ->toArray();
    }
}
