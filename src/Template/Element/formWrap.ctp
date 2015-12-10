<div class="row">
	<?= $this->element('imageForm') ?>
</div>

<div class="row" id="sortableImages">
	<?= $this->element('imagesContainer') ?>
</div>

<?= $this->Html->script('ajax_link_actions-compiled') ?>
<script>
	jQuery(document).ready(function ($) {
		initImageForms();
	});

	function initImageForms() {
		$('#sortableImages').sortable(
			{
				//				placeholder: "ui-state-highlight",
				update: function (event, ui) {
					$(this).find('[name="images[field_index]"]').each(function (key, value) {
						if ($(this).val() != (key + 1)) {
							$(this).val(key + 1).submit();
						}
					});
				}
			});


		var $form = $("form.image_save");
		$form.off('submit');
		$form.on('submit', function (e) {
			e.preventDefault();
			var actionUrl = e.currentTarget.action;
			var $alert;
			var that = this;
			document.that = $(this);
			$(this).find('button').addClass('disabled');
			$.ajax({
					   url     : actionUrl,
					   type    : 'post',
					   dataType: 'json',
					   data    : $(this).serialize(),
					   success : function (data) {
						   $alert = $(that).find('.alert-success');
					   },
					   error   : function () {
						   $alert = $(that).find('.alert-success');
					   },
					   complete: function () {
						   $alert
							   .hide()
							   .removeClass('hide')
							   .show('slow');
						   setTimeout(function () {
							   $alert.hide('slow');
						   }, 2000);
						   $(that).find('button').removeClass('disabled');
					   }
				   });

		});

		$form.find('[name="images[main]"]').off('change').on('change', function (e) {
			$form.find('[name="images[main]"]').filter(':checked').not(this).prop('checked', false).closest('form').submit();
			$(this).prop('checked', true).closest('form').submit();
		});
	}
</script>