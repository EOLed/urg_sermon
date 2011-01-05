<div class="sermons form">
<?php echo $this->Form->create('Sermon');?>
	<fieldset>
 		<legend><?php __('Edit Sermon'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('series_id');
		echo $this->Form->input('pastor_id');
		echo $this->Form->input('post_id');
		echo $this->Form->input('speaker_name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $this->Form->value('Sermon.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Sermon.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Sermons', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Posts', true), array('controller' => 'posts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Post', true), array('controller' => 'posts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Passages', true), array('controller' => 'passages', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Passage', true), array('controller' => 'passages', 'action' => 'add')); ?> </li>
	</ul>
</div>