<?php

class UserGroup extends OModule
{
	public $name="";
	public static $mysql_table_name="UserGroup";
        
        public function __construct($params = array())
        {
            $this->name = Language::string(79);
            parent::__construct($params);
        }
	
	public function mysql_delete()
	{
		$this->clear_object_links(User::get_mysql_table());
		$this->mysql_delete_object();
	}
}

?>