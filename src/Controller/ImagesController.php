<?php
	namespace Image\Controller;

	class ImagesController extends AppController {

		/**
		 * Vrátí názvy odpovídající požadavku (včetně vynechaných znaků)
		 *
		 * @return void
		 */
		public function images($modelName = null, $id = null) {
			$this->loadModel($modelName);
			$contentPage = $this->ContentPages->get($id);

			if ($this->request->is(['patch', 'post', 'put'])) {

				$contentPage = $this->ContentPages->patchEntity($contentPage, $this->request->data);
				if ($this->ContentPages->save($contentPage)) {
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

			$this->set('entity', $contentPage);


			if ($this->request->is('ajax')) {
				if (isset($this->request->data['images']['id'])) { //Změna obrázku

				} else { //Přidání nového
					if ($this->request->is('put')) {
						$images              = $this->ContentPages->Images->find()
							->limit(sizeof($this->request->data['images']) - 1)//Kolik se poslalo obrázků - extra data
							->order(['id' => 'DESC'])
							->all()
							->toArray();
						$contentPage->images = $images;
						$this->set('entity', $contentPage); //Nastavení entity jen s novými obrázky
					}
					$this->viewBuilder()->layout('ajax');

					$this->render('/Element/imagesContainer');
				}
			}
		}
	}
