<?
class stdctrls_IHTMLibrary {
	function Save($args)	{
		$__=&$GLOBALS['_STRINGS']['_'];
		$_ =&$GLOBALS['_STRINGS']['stdctrls'];

		extract(param_extract(array(
		BindTo=>'string',
		NewID=>'int'
		),$_POST));

		global $cfg;
		if ($BindTo) DBExec ("REPLACE INTO stdctrls_Libitems (BindTo,LibItemID) VALUES ('$BindTo',$NewID)");
		
		return array(ModalResult=>true);
	}
}
?>
