<div class="sermons view">
<h2><?php  __('Sermon');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $sermon['Sermon']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Series Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $sermon['Sermon']['series_id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Pastor Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $sermon['Sermon']['pastor_id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Post'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($sermon['Post']['title'], array('controller' => 'posts', 'action' => 'view', $sermon['Post']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Speaker Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $sermon['Sermon']['speaker_name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $sermon['Sermon']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $sermon['Sermon']['modified']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Sermon', true), array('action' => 'edit', $sermon['Sermon']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Sermon', true), array('action' => 'delete', $sermon['Sermon']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $sermon['Sermon']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Sermons', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Sermon', true), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Posts', true), array('controller' => 'posts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Post', true), array('controller' => 'posts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Passages', true), array('controller' => 'passages', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Passage', true), array('controller' => 'passages', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php __('Related Passages');?></h3>
	<?php if (!empty($sermon['Passage'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Id'); ?></th>
		<th><?php __('Sermon Id'); ?></th>
		<th><?php __('Passage'); ?></th>
		<th><?php __('Created'); ?></th>
		<th><?php __('Modified'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($sermon['Passage'] as $passage):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $passage['id'];?></td>
			<td><?php echo $passage['sermon_id'];?></td>
			<td><?php echo $passage['passage'];?></td>
			<td><?php echo $passage['created'];?></td>
			<td><?php echo $passage['modified'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'passages', 'action' => 'view', $passage['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'passages', 'action' => 'edit', $passage['id'])); ?>
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'passages', 'action' => 'delete', $passage['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $passage['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Passage', true), array('controller' => 'passages', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
