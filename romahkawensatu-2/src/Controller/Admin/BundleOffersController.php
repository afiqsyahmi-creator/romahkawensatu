<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\Exception\RecordNotFoundException;

class BundleOffersController extends AppController
{
    public function index(): void
    {
        $bundles = $this->fetchTable('BundleOffers')->find()
            ->contain(['Addon1', 'Addon2'])
            ->orderBy(['bundle_id' => 'ASC']);
        $this->set('bundles', $this->paginate($bundles));
    }

    public function add()
    {
        $table = $this->fetchTable('BundleOffers');
        $bundle = $table->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            // Ensure addon_id_1 < addon_id_2 for CHECK constraint
            $id1 = (int)$data['addon_id_1'];
            $id2 = (int)$data['addon_id_2'];
            if ($id1 === $id2) {
                $this->Flash->error('Bundle must contain two different add-ons.');
                $this->set(compact('bundle'));
                return;
            }
            if ($id1 > $id2) {
                [$data['addon_id_1'], $data['addon_id_2']] = [$id2, $id1];
            }
            $bundle = $table->patchEntity($bundle, $data);
            if ($table->save($bundle)) {
                $this->Flash->success('Bundle offer created.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not save the bundle offer.');
        }

        $addons = $this->fetchTable('Addons')->find()
            ->where(['status' => 'active'])
            ->orderBy(['addon_name' => 'ASC'])
            ->all();
        $this->set(compact('bundle', 'addons'));
    }

    public function edit($id)
    {
        $table = $this->fetchTable('BundleOffers');
        try {
            $bundle = $table->get($id);
        } catch (RecordNotFoundException $e) {
            $this->Flash->error('Bundle offer not found.');
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();
            $id1 = (int)$data['addon_id_1'];
            $id2 = (int)$data['addon_id_2'];
            if ($id1 === $id2) {
                $this->Flash->error('Bundle must contain two different add-ons.');
                $this->set(compact('bundle'));
                return;
            }
            if ($id1 > $id2) {
                [$data['addon_id_1'], $data['addon_id_2']] = [$id2, $id1];
            }
            $bundle = $table->patchEntity($bundle, $data);
            if ($table->save($bundle)) {
                $this->Flash->success('Bundle offer updated.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not update the bundle offer.');
        }

        $addons = $this->fetchTable('Addons')->find()
            ->where(['status' => 'active'])
            ->orderBy(['addon_name' => 'ASC'])
            ->all();
        $this->set(compact('bundle', 'addons'));
    }

    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->fetchTable('BundleOffers');
        try {
            $bundle = $table->get($id);
            $table->deleteOrFail($bundle);
            $this->Flash->success('Bundle offer deleted.');
        } catch (RecordNotFoundException $e) {
            $this->Flash->error('Bundle offer not found.');
        } catch (\Exception $e) {
            $this->Flash->error('Cannot delete this bundle offer.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
