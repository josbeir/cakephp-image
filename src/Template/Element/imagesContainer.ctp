<?php
	/* @var $this \App\View\AppView */
	//TODO aktivní
	use Cake\Utility\Inflector;

	foreach ($entity->images as $key => $image) {
		if (!is_numeric($key)) {
			continue;
		}
		?>
		<div class="well well-sm">
			<?= $this->Html->link(
				'<span aria-hidden="true">&times;</span>',
				"/Image/delete/{$image->id}",
				[
					'escape'               => false,
					'class'                => 'close',
					'data-ajax',
					'data-confirm'         => __('Are you sure that you want to delete this image?'),
					'data-success'         => 'delete-parent .well',
					'data-success-message' => __('Image successfully deleted')
				]
			) ?>
			<?= $this->Form->create($entity, [
				'type'  => 'file', 'horizontal' => true, 'cols' => [
					'label' => 4,
					'input' => 8,
					'error' => 12
				],
				'class' => 'image_save'
			]); ?>
			<?php $this->Form->templates([
				'checkboxContainer' => '<div class="form-group"><div class="col-md-12"><div class="checkbox">{{content}}</div></div></div>'
			]); ?>
			<div class="row">

				<div class="col-xs-2">
					<?= $this->Image->render($image, ['preset' => 'thumbnail', 'class' => 'img-rounded img-responsive']) ?>
				</div>
				<div class="col-xs-10">
					<div class="row">
						<?= $this->Form->input('images.id', ['value' => $image->id, 'type' => 'hidden']) ?>

						<?= $this->Form->hidden('images.field_index', [
								'type' => 'numeric', 'value' => $image->field_index, 'id' => 'images.id' . $image->field_index
							]
						) ?>
						<div class="col-xs-8">
							<?= $this->Form->input('images.title', ['value' => $image->title, 'id' => 'images.title' . $image->field_index]) ?>
						</div>
						<div class="col-xs-2">
							<?= $this->Form->input('images.active', ['type' => 'checkbox', 'checked' => $image->active, 'id' => 'images.active' . $image->field_index]); //, 'data-toggle' => 'toggle', 'label' => false, 'data-on' => __('active'), 'data-off' => __('Inactive')]) ?>
						</div>
						<div class="col-xs-2 ">
							<?= $this->Form->input('images.main', ['type' => 'checkbox', 'checked' => $image->main, 'id' => 'images.main' . $image->field_index]) ?>
						</div>
					</div>
					<div class="row last">
						<div class="col-xs-10">
							<?= $this->Form->input('images.description', ['value' => $image->description, 'id' => 'images.description' . $image->field_index]) ?>
						</div>
						<div class="col-xs-2 text-right">
							<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
						</div>
					</div>
				</div>

			</div>
			<div class="row">
				<div class="alert alert-success hide text-center" role="alert"><?= __('Saved') ?> </div>
				<div class="alert alert-danger hide text-center"
					 role="alert"><?= __('Something went wrong') ?> </div>
			</div>
			<?= $this->Form->end() ?>

		</div>
	<?php } ?>
