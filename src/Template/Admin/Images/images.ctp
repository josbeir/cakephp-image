<div class="actions columns col-lg-2 col-md-3">
	<ul class="nav nav-stacked nav-pills">
		<li><?= $this->Html->link(__('Go back'), $this->request->referer(), ['class' => 'btn btn-primary']) ?> </li>
	</ul>
</div>
<div class="content form col-lg-10 col-md-9 columns">
	<?= $this->element('formWrap') ?>
</div>
