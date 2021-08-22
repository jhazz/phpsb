<?
class doc_IDiagrams
{
	var $CopyrightText="(c)2007 PHP Systems builder. Documents";
	var $CopyrightURL="http://www.phpsb.com/doc";
	var $ComponentVersion="1.0";
	var $RoleAccess=array(DocumentDesigner=>"Edit");

	function Edit($args)
	{
		global $_SESSION,$cfg;
$flashVars="SessionKey=$_SESSION->SessionKey";
$bgColor="#ffffff";
$movieSrc="$cfg[PublicURL]/doc/doc_diagrams_edit.swf?rand=".rand();
?>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="100%" height="100%" id="preloader" align="middle">
<param name="allowScriptAccess" value="always" />
<param name="movie" value="<? print $movieSrc; ?>" />
<param name="FlashVars" value="<? print $flashVars; ?>" />
<param name="quality" value="high" />
<param name="wmode" value="opaque" />
<param name="bgcolor" value="<? print $bgColor; ?>" />
<embed src="<? print $movieSrc; ?>" wmode="opaque" FlashVars="<? print $flashVars; ?>" quality="high" bgcolor="<? print $bgColor; ?>" width="100%" height="100%" name="preloader" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
<?
	}

}
?>