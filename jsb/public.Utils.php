<?
class jsb_Utils{
	var $PagesByContext;
	var $LoadingContexts;
	
	function _walker_build_tree(&$row,$key,$args) {
		if ($key==0) return;
		$ParentID=$row['ParentID'];
		$tree=&$args[0];
		$BindParentInstead=$args[2];
		$BindParentTo=&$args[3];
		$ParentID=intval($ParentID);
		$tree[$ParentID]['_childs'][]=&$row;
		if ($BindParentTo) {
			if ($ParentID==$BindParentInstead) {
				$BindParentTo['_childs'][]=&$row;
			}
		}
		if ($args[1]) { # doAttachTrees
	    parse_str ($row['Options'],$row['_options']);
	    $v=$row['_options']['virtual'];
	    if ($v) {
	    	list ($BindingContext,$BindingJSBPageID)=explode ("/",$v);
	    	if ($this->jsb_Utils->LoadingContexts[$BindingContext]!=2) {
	    		$this->LoadJSBPages($BindingContext,true,true);
	    	}
    		$row['_virtual_page']=$this->PagesByContext[$BindingContext][$BindingJSBPageID];
    		$row['_virtual_context']=$BindingContext;
    		$row['_virtual_pageid']=$BindingJSBPageID;
	    }
	    
	    $a=$row['_options']['attach'];
	    if ($a) {
	    	list ($BindingContext,$BindingJSBPageID)=explode ("/",$a);
	    	if ($this->jsb_Utils->LoadingContexts[$BindingContext]!=2) {
	    		$this->LoadJSBPages($BindingContext,true,true,$BindingJSBPageID,&$row);
	    	}
    		$row['_attached_context']=$BindingContext;
    		$row['_attached_pageid']=$BindingJSBPageID;
	    }
		}
	}
	
	function &LoadJSBPages($SysContext,$BuildTree=false,$doAttachTrees=false,$BindParentInstead=0,$BindParentTo=false) {
		if ($this->LoadingContexts[$SysContext]==2) {return;}
	  $this->LoadingContexts[$SysContext]=2;
	  
		DBQuery2Array ("SELECT SysContext,JSBPageID,ParentID,Caption,JSBLayoutID,Options
		  FROM jsb_Pages WHERE SysContext='$SysContext' AND State=1 ORDER BY OrderNo",&$this->PagesByContext[$SysContext],"JSBPageID");
		
		if ($BuildTree && is_array($this->PagesByContext[$SysContext])) array_walk($this->PagesByContext[$SysContext],array($this,"_walker_build_tree"),array(&$this->PagesByContext[$SysContext],$doAttachTrees,$BindParentInstead,&$BindParentTo));
		return $this->PagesByContext[$SysContext];
	}
}

?>