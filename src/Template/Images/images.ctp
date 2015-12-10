<div class="actions columns col-lg-2 col-md-3">
	<h3><?= __('Actions') ?></h3>
	<ul class="nav nav-stacked nav-pills">
		<li class="active disabled"><?= $this->Html->link(__('Edit Content Page'), ['action' => 'edit', $entity->id]) ?> </li>
		<li><?= $this->Form->postLink(
				__('Delete'),
				['action' => 'delete', $entity->id],
				['confirm' => __('Are you sure you want to delete # {0}?', $entity->id), 'class' => 'btn-danger']
			)
			?></li>
		<li><?= $this->Html->link(__('New Content Page'), ['action' => 'add']) ?></li>
		<li><?= $this->Html->link(__('List Content Pages'), ['action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('List Layouts'), ['controller' => 'Layouts', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Layout'), ['controller' => 'Layouts', 'action' => 'add']) ?> </li>
	</ul>
</div>
<div class="contentPages form col-lg-10 col-md-9 columns">

	<?= $this->element('formWrap') ?>

</div>
