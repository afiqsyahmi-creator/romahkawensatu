<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;

class AppController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
        // Authentication component is available everywhere; each controller
        // decides which actions are public.
        $this->loadComponent('Authentication.Authentication');
    }
}
