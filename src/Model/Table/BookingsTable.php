<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class BookingsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('booking');
        $this->setPrimaryKey('booking_id');
        $this->setDisplayField('booking_id');
        $this->addBehavior('Timestamp');

        $this->belongsTo('Customers', ['foreignKey' => 'customer_id']);
        $this->belongsTo('Studios', ['foreignKey' => 'studio_id']);
        $this->hasMany('Payments', ['foreignKey' => 'booking_id']);
        $this->belongsToMany('Addons', [
            'through' => 'BookingAddons',
            'foreignKey' => 'booking_id',
            'targetForeignKey' => 'addon_id',
        ]);
    }

    /**
     * The overlap check. Returns true if the studio is already taken for any
     * part of the requested window on that date (pending or confirmed block it).
     * Times are "HH:MM" or "HH:MM:SS".
     */
    public function slotIsTaken(int $studioId, string $date, string $start, string $end, ?int $excludeId = null): bool
    {
        // Normalize date to YYYY-MM-DD (handles M/D/YY and other formats)
        $ts = strtotime($date);
        if ($ts !== false) {
            $date = date('Y-m-d', $ts);
        }

        // Normalize times to HH:MM (handles 12-hour like "6:00 PM")
        $tsStart = strtotime($start);
        if ($tsStart !== false) {
            $start = date('H:i', $tsStart);
        }
        $tsEnd = strtotime($end);
        if ($tsEnd !== false) {
            $end = date('H:i', $tsEnd);
        }

        $query = $this->find()
            ->where([
                'studio_id' => $studioId,
                'booking_date' => $date,
                'booking_status IN' => ['pending', 'confirmed'],
                'start_time <' => $end,   // existing starts before new ends
                'end_time >'  => $start,  // existing ends after new starts
            ]);
        if ($excludeId !== null) {
            $query->where(['booking_id !=' => $excludeId]);
        }
        return $query->count() > 0;
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->date('booking_date')->notEmptyDate('booking_date')
            ->time('start_time')->notEmptyTime('start_time')
            ->time('end_time')->notEmptyTime('end_time')
            ->numeric('total_price')
            ->naturalNumber('pax', 'Enter a valid number of pax')
            ->allowEmptyString('event_type')
            ->allowEmptyString('notes');
        return $validator;
    }

    /** Defense in depth: refuse to save an overlapping booking even if a
     *  controller forgets to check. */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(function ($entity) {
            return !$this->slotIsTaken(
                (int)$entity->studio_id,
                (string)$entity->booking_date,
                (string)$entity->start_time,
                (string)$entity->end_time,
                $entity->isNew() ? null : (int)$entity->booking_id
            );
        }, 'noOverlap', [
            'errorField' => 'start_time',
            'message' => 'This studio is already booked for that date and time.',
        ]);
        return $rules;
    }
}
