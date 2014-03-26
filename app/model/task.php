<?php

class task extends Record{

	const Table = 'tasks';
	
	public $id;

	public $creat_by;

	public $user_id;

	public $third_type;

	public $third_id;

	public $date_add;

	public $date_expire;

	public $priority;

	public $process;

	public $task;

	public $link;

	public $date_process;

	public $process_by;

	public $guid;
}