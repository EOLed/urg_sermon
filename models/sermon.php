<?php
class Sermon extends AppModel {
    var $name = 'Sermon';
    var $validate = array(
        'post_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
    );
    //The Associations below have been created with all possible keys, those that are not needed can be removed

    var $belongsTo = array(
        'Post' => array(
            'className' => 'Post',
            'foreignKey' => 'post_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Series' => array(
            'className' => 'Group',
            'foreignKey' => 'series_id'
        ),
        'Pastors' => array(
            'className' => 'Group',
            'foreignKey' => 'pastor_id'
        )
    );
}
?>
