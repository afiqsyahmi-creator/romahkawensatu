<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class AddonsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('addon');
        $this->setPrimaryKey('addon_id');
        $this->setDisplayField('addon_name');
        $this->belongsToMany('Bookings', [
            'through' => 'BookingAddons',
            'foreignKey' => 'addon_id',
            'targetForeignKey' => 'booking_id',
        ]);
    }

    /**
     * Calculate which add-ons are "popular" based on actual booking frequency
     * in the last 30 days. Returns addon_id values ordered by count DESC.
     */
    public function getPopularAddonIds(int $limit = 2): array
    {
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        return $this->find()
            ->select(['addon_id', 'cnt' => 'COUNT(BookingAddons.booking_id)'])
            ->innerJoinWith('BookingAddons', function ($q) use ($thirtyDaysAgo) {
                return $q->innerJoinWith('Bookings', function ($q2) use ($thirtyDaysAgo) {
                    return $q2->where(['Bookings.created_at >=' => $thirtyDaysAgo]);
                });
            })
            ->group(['Addons.addon_id'])
            ->orderBy(['cnt' => 'DESC'])
            ->limit($limit)
            ->all()
            ->extract('addon_id')
            ->toList();
    }

    /**
     * Refresh the is_popular flag on the addon table.
     * Call this from a nightly cron job.
     */
    public function refreshPopularFlag(int $limit = 2): void
    {
        // Reset all
        $this->updateAll(['is_popular' => 0], []);

        // Set top N
        $popularIds = $this->getPopularAddonIds($limit);
        if (!empty($popularIds)) {
            $this->updateAll(
                ['is_popular' => 1],
                ['addon_id IN' => $popularIds]
            );
        }
    }

    /**
     * Get remaining weekly capacity for an add-on.
     * Returns null if unlimited, or the remaining slots.
     */
    public function getRemainingCapacity(int $addonId): ?int
    {
        $addon = $this->get($addonId);
        if ($addon->weekly_capacity === null) {
            return null; // unlimited
        }
        return max(0, (int)$addon->weekly_capacity - (int)$addon->weekly_booked);
    }
}
