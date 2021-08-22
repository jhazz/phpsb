<body <? if ($_GET['bgcolor']) print "bgcolor='$_GET[bgcolor]'"; ?> topmargin="0" leftmargin="0" marginheight="0" marginwidth="0"><script src='/public/sys/putswf.js'></script>
<script>
<?
$s="";
foreach ($_GET as $k=>$v) {$s.=(($s)?",":"")."$k:'$v'";}
print "PutSwf({"."$s});";
?>
</script></body>