<div class="sermons index">
    <h2><?php __('Sermons');?></h2>
    <table cellpadding="0" cellspacing="0">
    <tr>
            <th><?php echo $this->Paginator->sort('id');?></th>
            <th><?php echo $this->Paginator->sort('series_id');?></th>
            <th><?php echo $this->Paginator->sort('passages');?></th>
            <th><?php echo $this->Paginator->sort('pastor_id');?></th>
            <th><?php echo $this->Paginator->sort('post_id');?></th>
            <th><?php echo $this->Paginator->sort('speaker_name');?></th>
            <th><?php echo $this->Paginator->sort('created');?></th>
            <th class="actions"><?php __('Actions');?></th>
    </tr>
    <?php
    $i = 0;
    foreach ($sermons as $sermon):
        $class = null;
        if ($i++ % 2 == 0) {
            $class = ' class="altrow"';
        }
    ?>
    <tr<?php echo $class;?>>
        <td><?php echo $sermon['Sermon']['id']; ?>&nbsp;</td>
        <td><?php echo $sermon['Series']['name']; ?>&nbsp;</td>
        <td><?php echo $sermon['Sermon']['passages']; ?>&nbsp;</td>
        <td><?php echo $sermon['Pastor']['name']; ?>&nbsp;</td>
        <td>
            <?php echo $this->Html->link($sermon['Post']['title'], array('controller' => 'posts', 'action' => 'view', $sermon['Post']['id'])); ?>
        </td>
        <td><?php echo $sermon['Sermon']['speaker_name']; ?>&nbsp;</td>
        <td><?php echo $sermon['Sermon']['created']; ?>&nbsp;</td>
        <td class="actions">
            <?php echo $this->Html->link(__('View', true), array('action' => 'view', $sermon['Sermon']['id'])); ?>
            <?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $sermon['Sermon']['id'])); ?>
            <?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $sermon['Sermon']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $sermon['Sermon']['id'])); ?>
        </td>
    </tr>
<?php endforeach; ?>
    </table>
    <p>
    <?php
    echo $this->Paginator->counter(array(
    'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
    ));
    ?>  </p>

    <div class="paging">
        <?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
     |  <?php echo $this->Paginator->numbers();?>
 |
        <?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
    </div>
</div>
<div class="actions">
    <h3><?php __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('New Sermon', true), array('action' => 'add')); ?></li>
    </ul>
</div>
