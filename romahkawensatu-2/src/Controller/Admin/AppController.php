<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController as BaseController;
use Cake\Event\EventInterface;

/**
 * Base for every Admin-prefixed controller. Identity is required for all
 * actions except login/logout — this is the server-side gate.
 */
class AppController extends BaseController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login', 'logout']);
    }

    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);
        // Authenticated admin pages use the admin layout (with the nav).
        if ($this->getRequest()->getAttribute('identity')) {
            $this->viewBuilder()->setLayout('admin');
        }
    }
}
