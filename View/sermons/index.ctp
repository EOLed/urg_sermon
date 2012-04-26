<div class="sermons index span12">
    <h2><?php echo __('Sermons');?></h2>
    <table cellpadding="0" cellspacing="0">
    <tr>
            <th><?php echo $this->Paginator->sort('Post.publish_timestamp');?></th>
            <th><?php echo $this->Paginator->sort('Series.name');?></th>
            <th><?php echo $this->Paginator->sort('Post.title');?></th>
            <th><?php echo $this->Paginator->sort('passages');?></th>
            <th><?php echo $this->Paginator->sort('Pastor.name');?></th>
            <th class="actions"><?php echo __('Actions');?></th>
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
        <td>
            <?php
                if ($sermon["Post"]["publish_timestamp"] == null || 
                        $sermon["Post"]["publish_timestamp"] == "") {
                    echo __("Draft");
                } else {
                    echo $this->Time->format("n/j/y", $sermon['Post']['publish_timestamp']);
                }
            ?>
        </td>
        <td><?php echo $sermon['Series']['name']; ?>&nbsp;</td>
        <td><?php echo $sermon['Post']['title'] ?>&nbsp;</td>
        <td><?php echo $sermon['Sermon']['passages']; ?>&nbsp;</td>
        <td><?php echo isset($sermon['Pastor']['name']) ? $sermon["Pastor"]["name"] : $sermon["Sermon"]["speaker_name"]; ?>&nbsp;</td>
        <td class="actions">
            <?php echo $this->Html->link(__('View'), array('action' => 'view', $sermon['Sermon']['id'])); ?>
            <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $sermon['Sermon']['id'])); ?>
            <?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $sermon['Sermon']['id']), null, sprintf(__('Are you sure you want to delete # %s?'), $sermon['Sermon']['id'])); ?>
        </td>
    </tr>
<?php endforeach; ?>
    </table>
    <p>
    <?php
    echo $this->Paginator->counter(array(
    'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%')
    ));
    ?>  </p>

    <div class="paging">
        <?php echo $this->Paginator->prev('<< ' . __('previous'), array(), null, array('class'=>'disabled'));?>
     |  <?php echo $this->Paginator->numbers();?>
 |
        <?php echo $this->Paginator->next(__('next') . ' >>', array(), null, array('class' => 'disabled'));?>
    </div>
</div>
<div class="actions span12">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('New Sermon'), array('action' => 'add')); ?></li>
    </ul>
</div>
