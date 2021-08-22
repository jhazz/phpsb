<?
function core_PutSwf(&$args)
  {
  global $cfg;
  if (!$_ENV->PutSwfInited) {
  	$_ENV->PutSwfInited=1; 
  	print "</script><script src='$cfg[PublicURL]/sys/putswf.js'></script>\n";
  }

  $s="";
  foreach($args as $k=>$v) {
  	if ($k=='ToString') continue;
  	$s.=(($s)?",":"")."$k:'$v'";
  }
	$result="<script>PutSwf({"."$s});</script>";
  if ($args['ToString']) return $result;
  print $result;
  }


?>
