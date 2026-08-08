<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\Exception\RecordNotFoundException;

class AddonsController extends AppController
{
    public function index(): void
    {
        $addons = $this->fetchTable('Addons')->find()->orderBy(['addon_id' => 'ASC']);
        $this->set('addons', $this->paginate($addons));
    }

    public function add()
    {
        $table = $this->fetchTable('Addons');
        $addon = $table->newEmptyEntity();
        if ($this->request->is('post')) {
            $addon = $table->patchEntity($addon, $this->request->getData());
            if ($table->save($addon)) {
                $this->Flash->success('Add-on created.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not save the add-on.');
        }
        $this->set('addon', $addon);
        $this->set('statuses', ['active' => 'active', 'inactive' => 'inactive']);
        $this->set('selectionTypes', ['toggle' => 'Toggle (on/off)', 'quantity' => 'Quantity (+/− stepper)']);
    }

    public function edit($id)
    {
        $table = $this->fetchTable('Addons');
        $addon = $table->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $addon = $table->patchEntity($addon, $this->request->getData());
            if ($table->save($addon)) {
                $this->Flash->success('Add-on updated.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not save the add-on.');
        }
        $this->set('addon', $addon);
        $this->set('statuses', ['active' => 'active', 'inactive' => 'inactive']);
        $this->set('selectionTypes', ['toggle' => 'Toggle (on/off)', 'quantity' => 'Quantity (+/− stepper)']);
    }

    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->fetchTable('Addons');
        try {
            $addon = $table->get($id);
            $table->deleteOrFail($addon);
            $this->Flash->success('Add-on deleted.');
        } catch (RecordNotFoundException $e) {
            $this->Flash->error('Add-on not found.');
        } catch (\Exception $e) {
            $this->Flash->error('Cannot delete: this add-on is used by bookings. Set it inactive instead.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
