<?= $this->Form->create($entity, [
	'type' => 'file', 'horizontal' => true, 'id' => 'imageUpload', 'cols' => [
		'label' => 4,
		'input' => 8,
		'error' => 12
	]
]); ?>

<div class="well well-sm text-center">
	<fieldset>
		<legend><?= __('Add new image') ?></legend>

		<div class="row">
			<div class="col-xs-4">
				<?= $this->Form->input('images.extra_data.title'); ?>
			</div>
			<div class="col-xs-8">
				<?= $this->Form->input('images.extra_data.description'); ?>
			</div>
		</div>
		<div class="row text-right">
			<div class="col-xs-12">
				<?= $this->Form->file('images.', [
					'type'         => 'image',
					'multiple'     => true,
					'id'           => 'fileUpload',
					'button-label' => __('Choose files to upload them')
				]); ?>
			</div>
		</div>
	</fieldset>
	<br>
	<div class="row">
		<div class="col-xs-12">
			<div id="progress" class="progress">
				<div class="progress-bar progress-bar-success"></div>
			</div>
		</div>
	</div>

	<div class="row" id="dropZone">
		<div class="col-xs-12">
			<span class="glyphicon glyphicon-plus giant"></span>
			<div class="row">
				<small><?= __('You can drop images over here') ?></small>
			</div>
		</div>
	</div>
</div>
<?= $this->Form->end() ?>


<script>
	$(function () {
		'use strict';
		var url = window.location;
		$('#fileUpload').fileupload(
			{
				url        : url,
				//				dataType   : 'json',
				done       : function (e, data) {
					$('#sortableImages').append(data.result);
					initImageForms();

					$('#progress').removeClass('active');//.find('.progress-bar').animate({'width': 0 + '%'}, 0);
					toastr.success('<?= __('Image uploaded') ?>');
//					$('[data-toggle=toggle]').bootstrapToggle();
				},
				error      : function () {
					toastr.error('<?= __('Something went wrong') ?>');
				},
				progressall: function (e, data) {
					var progress = parseInt(data.loaded / data.total * 100, 10);
					$('#progress').addClass('active').find('.progress-bar')
						.css(
							'width',
							progress + '%'
						);
				}
			}).prop('disabled', !$.support.fileInput)
			.parent().addClass($.support.fileInput ? undefined : 'disabled');


		$("#dropZone")
			.on("dragover", function (event) {
				$(this).addClass('dragging');
			})
			.on("dragleave", function (event) {
				$(this).removeClass('dragging');
			})
			.on("drop", function (event) {
				$(this).removeClass('dragging');
			});
	});
</script>

