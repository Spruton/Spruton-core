<?php

class fieldtype_action
{
  public $options;
  
  function __construct()
  {
    $this->options = array('name'=>TEXT_FIELDTYPE_ACTION_TITLE);
  }
  
  function output($options)
  {
  	global $app_user;
  	
    $list = array();
    
    $access_rules = new access_rules($options['field']['entities_id'], $options['item']);
    
    $redirect_to = '&gotopage[' . $options['reports_id'] . ']=' . $_POST['page'];
    
    if(isset($options['redirect_to']))
    {
      if(strlen($options['redirect_to'])>0)
      {
        $redirect_to .= '&redirect_to=' . $options['redirect_to'];
      }
    }
    
    if(users::has_access('delete',$access_rules->get_access_schema()))
    {
    	$check = true;
    	
    	if(users::has_access('delete_creator',$access_rules->get_access_schema()) and $options['item']['created_by']!=$app_user['id'])
    	{
    		$check = false;
    	}
    	
    	if($check)
    	{	
      	$list[] = button_icon_delete(url_for('items/delete','id=' .$options['value'] . '&entity_id=' . $options['field']['entities_id'] . '&path=' . $options['path'] . $redirect_to));
    	}
    }
    
    if(users::has_access('update',$access_rules->get_access_schema()))
    {
      $list[] = button_icon_edit(url_for('items/form','id=' . $options['value']. '&entity_id=' . $options['field']['entities_id'] . '&path=' . $options['path'] . $redirect_to));
    }
    
    //change user pasword
    if(users::has_access('update',$access_rules->get_access_schema()) and $options['field']['entities_id']==1)
    {
      $list[] = button_icon(TEXT_CHANGE_PASSWORD,'fa fa-unlock-alt',url_for('items/change_user_password','path=' . $options['path'] . ($options['item']['parent_item_id']==0 ? '-' . $options['value']:'') . $redirect_to),false);
    }
    
    //login as user
    if($app_user['group_id']==0 and $options['field']['entities_id']==1)
    {
    	if($options['item']['field_5']==1)
    	{
    		$list[] = button_icon(TEXT_BUTTON_LOGIN,'fa fa-sign-in',url_for('users/login_as','users_id=' . $options['item']['id']),true);
    	}
    }
    
    //check access to action with assigned only
    if($options['hide_actions_buttons']==1)
    {	
    	$list = array();    	
    }
    else
    {
	    if(users::has_users_access_name_to_entity('action_with_assigned',$options['field']['entities_id']))
	    {
	    	if(!users::has_access_to_assigned_item($options['field']['entities_id'],$options['item']['id']))
	    	{
	    		$list = array();
	    	}
	    }
    }
    
    $list[] = button_icon(TEXT_BUTTON_INFO,'fa fa-info',url_for('items/info','path=' . (count($options['path_info']) ? $options['path'] : $options['path'] . '-' . $options['value']) . '&gotopage[' . $_POST['reports_id'] . ']=' . $_POST['page']),false);
    
    return implode(' ',$list);
  }
}