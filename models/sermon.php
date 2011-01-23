<?php
class Sermon extends UrgSermonAppModel {
    var $name = 'Sermon';
    var $validate = array(
        'post_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
		'series_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'display_speaker_name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
                'message' => 'sermons.errors.speaker.name.required',
                'required' => true,
                'allowEmpty' => false
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'series_name' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
                'message' => 'sermons.errors.series.name.required',
                'required' => true,
                'allowEmpty' => false
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
    );
    //The Associations below have been created with all possible keys, those that are not needed can be removed

    var $belongsTo = array(
        'Post' => array(
            'className' => 'UrgPost.Post',
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
