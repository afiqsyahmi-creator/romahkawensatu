<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\Exception\RecordNotFoundException;
use Psr\Http\Message\UploadedFileInterface;

class GalleriesController extends AppController
{
    private const MAX_BYTES = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    public function index(): void
    {
        $galleries = $this->fetchTable('Galleries')->find()
            ->contain(['Studios'])
            ->orderBy(['Galleries.studio_id' => 'ASC', 'sort_order' => 'ASC']);
        $this->set('galleries', $this->paginate($galleries));
    }

    public function add()
    {
        $table = $this->fetchTable('Galleries');
        $gallery = $table->newEmptyEntity();

        // Auto-suggest sort_order: next number for the selected studio
        $suggestedOrder = 0;
        $selectedStudio = (string)$this->request->getData('studio_id', '');
        if ($selectedStudio !== '') {
            $suggestedOrder = (int)$table->find()
                ->where(['studio_id' => $selectedStudio])
                ->count() + 1;
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // If a file was uploaded, use it; else fall back to manual path
            $file = $this->request->getData('image_file');
            $manualPath = (string)($data['image_path'] ?? '');

            if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                $up = $this->saveUpload($file);
                if (!$up['ok']) {
                    $this->Flash->error($up['error']);
                } else {
                    $data['image_path'] = $up['path'];
                    $gallery = $table->patchEntity($gallery, $data);
                    if ($table->save($gallery)) {
                        $this->Flash->success('Image added to the gallery.');
                        return $this->redirect(['action' => 'index']);
                    }
                    $this->Flash->error('Could not save. Please check the form fields.');
                }
            } elseif ($manualPath !== '') {
                // Manual path — trust it, store as-is
                $data['image_path'] = $manualPath;
                $gallery = $table->patchEntity($gallery, $data);
                if ($table->save($gallery)) {
                    $this->Flash->success('Image added to the gallery.');
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error('Could not save. Please check the form fields.');
            } else {
                $this->Flash->error('Please upload an image or provide an image path.');
            }
        }

        $this->set('gallery', $gallery);
        $this->set('studios', $this->fetchTable('Studios')->find('list'));
        $this->set('suggestedOrder', $suggestedOrder);
    }

    public function edit($id)
    {
        $table = $this->fetchTable('Galleries');
        $gallery = $table->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();
            $up = $this->saveUpload($this->request->getData('image_file'));
            if (!$up['ok']) {
                $this->Flash->error($up['error']);
            } else {
                if ($up['path']) {
                    $data['image_path'] = $up['path'];
                }
                $gallery = $table->patchEntity($gallery, $data);
                if ($table->save($gallery)) {
                    $this->Flash->success('Gallery image updated.');
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error('Could not save the image.');
            }
        }
        $this->set('gallery', $gallery);
        $this->set('studios', $this->fetchTable('Studios')->find('list'));
    }

    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->fetchTable('Galleries');
        try {
            $gallery = $table->get($id);
            $table->deleteOrFail($gallery);
            $this->Flash->success('Gallery image deleted.');
        } catch (RecordNotFoundException $e) {
            $this->Flash->error('Image not found.');
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Validate and store an uploaded image safely.
     * Returns ['ok' => bool, 'path' => ?string, 'error' => ?string].
     *
     * Hardening:
     *  - No file at all is allowed (admin can type a path instead).
     *  - Size capped at 5 MB.
     *  - MIME sniffed from the actual bytes with finfo — the client-sent
     *    filename and content-type are NOT trusted.
     *  - Filename is randomised; the original name is discarded so it can't
     *    smuggle an extension or path.
     */
    private function saveUpload(?UploadedFileInterface $file): array
    {
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null, 'error' => null];
        }
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'path' => null, 'error' => 'Image upload failed. Please try again.'];
        }
        if ($file->getSize() !== null && $file->getSize() > self::MAX_BYTES) {
            return ['ok' => false, 'path' => null, 'error' => 'Image is too large (max 5 MB).'];
        }

        $contents = (string)$file->getStream();
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        if (!isset(self::ALLOWED[$mime])) {
            return ['ok' => false, 'path' => null, 'error' => 'Only JPG, PNG, WEBP, or GIF images are allowed.'];
        }

        $ext = self::ALLOWED[$mime];
        $name = bin2hex(random_bytes(6)) . '_' . time() . '.' . $ext;
        $dir = WWW_ROOT . 'img' . DS . 'gallery' . DS;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (file_put_contents($dir . $name, $contents) === false) {
            return ['ok' => false, 'path' => null, 'error' => 'Could not write the image to disk.'];
        }
        return ['ok' => true, 'path' => 'img/gallery/' . $name, 'error' => null];
    }
}
