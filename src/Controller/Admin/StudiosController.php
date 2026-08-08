<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\Exception\RecordNotFoundException;
use Psr\Http\Message\UploadedFileInterface;

class StudiosController extends AppController
{
    public function index(): void
    {
        $studios = $this->fetchTable('Studios')->find()->orderBy(['studio_id' => 'ASC']);
        $this->set('studios', $this->paginate($studios));
    }

    public function add()
    {
        $table = $this->fetchTable('Studios');
        $studio = $table->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $up = $this->_saveUpload($this->request->getData('image_file'));
            if ($up['path']) {
                $data['image'] = $up['path'];
            } elseif (!$up['ok']) {
                $this->Flash->error($up['error']);
            }
            $studio = $table->patchEntity($studio, $data);
            if ($table->save($studio)) {
                $this->Flash->success('Studio added.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not save the studio.');
        }
        $this->set('studio', $studio);
        $this->set('statuses', ['active' => 'active', 'inactive' => 'inactive', 'maintenance' => 'maintenance']);
    }

    public function edit($id)
    {
        $table = $this->fetchTable('Studios');
        $studio = $table->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();
            $up = $this->_saveUpload($this->request->getData('image_file'));
            if ($up['path']) {
                $data['image'] = $up['path'];
            } elseif (!$up['ok']) {
                $this->Flash->error($up['error']);
            }
            $studio = $table->patchEntity($studio, $data);
            if ($table->save($studio)) {
                $this->Flash->success('Studio updated.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not save the studio.');
        }
        $this->set('studio', $studio);
        $this->set('statuses', ['active' => 'active', 'inactive' => 'inactive', 'maintenance' => 'maintenance']);
    }

    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->fetchTable('Studios');
        try {
            $studio = $table->get($id);
            $table->deleteOrFail($studio);
            $this->Flash->success('Studio deleted.');
        } catch (RecordNotFoundException $e) {
            $this->Flash->error('Studio not found.');
        } catch (\Exception $e) {
            // FK restrict: studio still has bookings
            $this->Flash->error('Cannot delete: this studio has bookings. Set it to inactive instead.');
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Save an uploaded cover image to img/gallery/ and return the path.
     */
    private function _saveUpload(?UploadedFileInterface $file): array
    {
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null, 'error' => null];
        }
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'path' => null, 'error' => 'Upload failed.'];
        }
        if ($file->getSize() !== null && $file->getSize() > 5 * 1024 * 1024) {
            return ['ok' => false, 'path' => null, 'error' => 'Image is too large (max 5 MB).'];
        }

        $contents = (string)$file->getStream();
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $ext = $allowed[$mime] ?? null;
        if (!$ext) {
            return ['ok' => false, 'path' => null, 'error' => 'Only JPG, PNG or WEBP images are allowed.'];
        }

        $name = 'studio_' . bin2hex(random_bytes(4)) . '_' . time() . '.' . $ext;
        $dir = WWW_ROOT . 'img' . DS . 'gallery' . DS;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        file_put_contents($dir . $name, $contents);

        return ['ok' => true, 'path' => 'img/gallery/' . $name, 'error' => null];
    }
}
