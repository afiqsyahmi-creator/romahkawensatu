<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

class GalleriesController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index']);
    }

    public function index()
    {
        $galleries = $this->fetchTable('Galleries')->find()
            ->contain(['Studios'])
            ->orderBy(['Galleries.studio_id' => 'ASC', 'sort_order' => 'ASC']);
        $this->set('galleries', $this->paginate($galleries));

        $studios = $this->fetchTable('Studios')->find('list', [
            'keyField' => 'studio_id',
            'valueField' => 'studio_name'
        ])->where(['status' => 'active'])->toArray();
        $this->set('studios', $studios);
    }

    public function add()
    {
        $table = $this->fetchTable('Galleries');
        $gallery = $table->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $file = $this->request->getData('image_file');
            $studioId = (string)($data['studio_id'] ?? '');

            if (empty($studioId)) {
                $this->Flash->error('Please select a studio.');
            } elseif ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                // Save uploaded file
                $up = $this->_saveUpload($file);
                if (!$up['ok']) {
                    $this->Flash->error($up['error']);
                } else {
                    $data['image_path'] = $up['path'];
                    $data['sort_order'] = (int)($data['sort_order'] ?? 0);
                    $gallery = $table->patchEntity($gallery, $data);
                    if ($table->save($gallery)) {
                        $this->Flash->success('Thank you! Your photo has been added to the gallery.');
                        return $this->redirect(['action' => 'index']);
                    }
                    $this->Flash->error('Could not save. Please check the form.');
                }
            } else {
                $this->Flash->error('Please select an image to upload.');
            }
        }

        $this->set('gallery', $gallery);
        $this->set('studios', $this->fetchTable('Studios')->find('list', ['keyField' => 'studio_id', 'valueField' => 'studio_name'])->where(['status' => 'active']));
    }

    /**
     * AJAX endpoint: upload an image for a specific studio.
     */
    public function apiUpload()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('json');

        if (!$this->request->is('post')) {
            $this->response = $this->response->withStringBody(json_encode(['ok' => false, 'error' => 'POST required']));
            return $this->response;
        }

        $studioId = $this->request->getData('studio_id');
        $file = $this->request->getData('image_file');

        if (empty($studioId)) {
            $this->response = $this->response->withStringBody(json_encode(['ok' => false, 'error' => 'No studio selected']));
            return $this->response;
        }

        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            $this->response = $this->response->withStringBody(json_encode(['ok' => false, 'error' => 'No file uploaded']));
            return $this->response;
        }

        $up = $this->_saveUpload($file);
        if (!$up['ok']) {
            $this->response = $this->response->withStringBody(json_encode(['ok' => false, 'error' => $up['error']]));
            return $this->response;
        }

        $table = $this->fetchTable('Galleries');
        $gallery = $table->newEmptyEntity();
        $gallery = $table->patchEntity($gallery, [
            'studio_id' => $studioId,
            'image_path' => $up['path'],
            'sort_order' => 0,
        ]);

        if ($table->save($gallery)) {
            $this->response = $this->response->withStringBody(json_encode(['ok' => true, 'path' => $up['path']]));
        } else {
            $this->response = $this->response->withStringBody(json_encode(['ok' => false, 'error' => 'Could not save']));
        }

        return $this->response;
    }

    /**
     * Validate and store an uploaded image.
     */
    private function _saveUpload($file): array
    {
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null, 'error' => null];
        }
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'path' => null, 'error' => 'Upload failed. Please try again.'];
        }
        if ($file->getSize() !== null && $file->getSize() > 5 * 1024 * 1024) {
            return ['ok' => false, 'path' => null, 'error' => 'Image is too large (max 5 MB).'];
        }

        $contents = (string)$file->getStream();
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!isset($allowed[$mime])) {
            return ['ok' => false, 'path' => null, 'error' => 'Only JPG, PNG, WEBP, or GIF images are allowed.'];
        }

        $ext = $allowed[$mime];
        $name = bin2hex(random_bytes(6)) . '_' . time() . '.' . $ext;
        $dir = WWW_ROOT . 'img' . DS . 'gallery' . DS;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (file_put_contents($dir . $name, $contents) === false) {
            return ['ok' => false, 'path' => null, 'error' => 'Could not save the image.'];
        }
        return ['ok' => true, 'path' => 'img/gallery/' . $name, 'error' => null];
    }
}
