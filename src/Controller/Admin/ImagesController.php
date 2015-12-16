<?php
	namespace Image\Controller\Admin;


	use Cake\Filesystem\File;
	use Cake\Filesystem\Folder;
	use Cake\Network\Exception\InternalErrorException;
	use Cake\ORM\TableRegistry;
	use Image\Controller\AppController;

	class ImagesController extends AppController {

		/**
		 * Vrátí názvy odpovídající požadavku (včetně vynechaných znaků)
		 *
		 * @return void
		 */
		public function images($modelName = null, $id = null) {
			$this->loadModel($modelName);
			$entity = $this->{$modelName}->get($id);

			if ($this->request->is(['patch', 'post', 'put'])) {

				$entity = $this->{$modelName}->patchEntity($entity, $this->request->data);
				if ($this->{$modelName}->save($entity)) {
					if (!$this->request->is('ajax')) {
						$this->Flash->success(__('The content page has been saved.'));

						return $this->redirect(['action' => 'index']);
					}
				} else {
					if (!$this->request->is('ajax')) {
						$this->Flash->error(__('The content page could not be saved. Please, try again.'));
					}
				}
			}

			$this->set('entity', $entity);


			if ($this->request->is('ajax')) {
				if (isset($this->request->data['images']['id'])) { //Změna obrázku

				} else { //Přidání nového
					if ($this->request->is('put')) {
						$images         = $this->{$modelName}->Images->find()
							->limit(sizeof($this->request->data['images']) - 1)//Kolik se poslalo obrázků - extra data
							->order(['id' => 'DESC'])
							->all()
							->toArray();
						$entity->images = $images;
						$this->set('entity', $entity); //Nastavení entity jen s novými obrázky
					}
					$this->viewBuilder()->layout('ajax');

					$this->render('/Element/imagesContainer');
				}
			}
		}


		public function ajaxDeleteImage($id = null) {
			$this->autoRender = false;
			$ImagesTable      = TableRegistry::get('Images');
			$image            = $ImagesTable->get($id);


			$shared = $ImagesTable->find()
				->where([
					'id !='          => $image->id,
					'field_index !=' => $image->field_index,
					'model'          => $image->model,
					'filename'       => $image->filename
				]);

			if (!$shared->count()) {
				$basePath = WWW_ROOT . 'uploads/images/' . $image->model;
				$dir      = new Folder($basePath);
				$files    = $dir->find('.*_.*\..*');

				(new File($basePath . DS . $image->filename))->delete();

				foreach ($files as $file) {
					if (preg_match("/.*_{$image->filename}/", $file)) {
						(new File($basePath . DS . $file))->delete();
					}
				}
			}


			if (!$ImagesTable->delete($image)) {
				throw new InternalErrorException;
			}

			if (!$this->request->is('ajax')) {
				$this->redirect($this->referer());
			}
		}
	}
