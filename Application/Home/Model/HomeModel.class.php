<?php
namespace Home\Model;
use Think\Model;
class HomeModel extends Model {

	function getone($sql){
		$Home = M(); 
    	$result = $Home->query($sql);
    	return $result[0];
	}

	function getlist($sql){
		$Home = M(); 
    	return $Home->query($sql);
	}

	//例：select count(*) as count from `my_home`
	function getcount($sql){
		$Home = M(); 
    	$result = $Home->query($sql);
    	return $result[0]['count'];
	}

	function del($sql){
		$Home = M();
		if($Home->query($sql) === false){
			return false;
		}else{
			return true;
		}
	}

	function update($table,$arr,$where){
		$Home = M($table); 
	    return $Home->where($where)->save($arr); 
	}

	function add($table,$arr){
		$Home = M($table);
	    return $Home->add($arr); 
	}


	function qeury($sql){
		$Home = M(); 
    	$Home->query($sql);
	}
}