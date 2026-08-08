<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

class StudiosController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index', 'view']);
    }

    /** Packages grid — shows first gallery image as cover */
    public function index(): void
    {
        $studios = $this->Studios->find()
            ->where(['status' => 'active'])
            ->contain(['Galleries' => function ($q) {
                return $q->orderBy(['sort_order' => 'ASC']);
            }])
            ->orderBy(['studio_id' => 'ASC']);
        $this->set(compact('studios'));
    }

    public function view(int $id): void
    {
        $studio = $this->Studios->get($id, contain: ['Galleries']);

        // Get all active studio IDs for prev/next navigation
        $allIds = $this->Studios->find()
            ->where(['status' => 'active'])
            ->orderBy(['studio_id' => 'ASC'])
            ->all()
            ->extract('studio_id')
            ->toArray();

        $prevId = null;
        $nextId = null;
        $pos = array_search($id, $allIds);
        if ($pos !== false) {
            if ($pos > 0) $prevId = $allIds[$pos - 1];
            if ($pos < count($allIds) - 1) $nextId = $allIds[$pos + 1];
        }

        $this->set(compact('studio', 'prevId', 'nextId'));
    }
}
