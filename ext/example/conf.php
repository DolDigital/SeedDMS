<?php
$EXT_CONF['example'] = array(
	'title' => 'Example Extension',
	'description' => 'This sample extension demonstrate the use of various hooks',
	'disable' => false,
	'version' => '1.0.0',
	'releasedate' => '2013-05-03',
	'author' => array('name'=>'Uwe Steinmann', 'email'=>'uwe@steinmann.cx', 'company'=>'MMK GmbH'),
	'config' => array(
		'input_field' => array(
			'title'=>'Example input field',
			'type'=>'input',
			'size'=>20,
		),
		'checkbox' => array(
			'title'=>'Example check box',
			'type'=>'checkbox',
		),
		'list' => array(
			'title'=>'Example select menu from options',
			'type'=>'select',
			'options' => array('Option 1', 'Option 2', 'Option 3'),
			'multiple' => true,
			'size' => 2,
		),
		'categories' => array(
			'title'=>'Example select menu from categories',
			'type'=>'select',
			'internal'=>'categories',
			'multiple' => true,
		),
		'users' => array(
			'title'=>'Example select menu from users',
			'type'=>'select',
			'internal'=>'users',
			'multiple' => true,
		),
		'groups' => array(
			'title'=>'Example select menu from groups',
			'type'=>'select',
			'internal'=>'groups',
			'multiple' => true,
		),
		'attributedefinitions' => array(
			'title'=>'Example select menu from attribute definitions',
			'type'=>'select',
			'internal'=>'attributedefinitions',
			'multiple' => true,
		),
	),
	'constraints' => array(
		'depends' => array('php' => '5.4.4-', 'seeddms' => '4.3.0-'),
	),
	'icon' => 'icon.png',
	'class' => array(
		'file' => 'class.example.php',
		'name' => 'SeedDMS_ExtExample'
	),
	'language' => array(
		'file' => 'lang.php',
	),
);
?>
