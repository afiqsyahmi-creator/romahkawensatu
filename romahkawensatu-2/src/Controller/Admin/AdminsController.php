<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class AdminsController extends AppController
{
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        if ($result && $result->isValid()) {
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }
        if ($this->request->is('post') && !($result && $result->isValid())) {
            $this->Flash->error('Invalid email or password.');
        }
    }

    public function logout()
    {
        $this->Authentication->logout();
        return $this->redirect(['controller' => 'Admins', 'action' => 'login']);
    }
}
