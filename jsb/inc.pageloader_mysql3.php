<?php
if ($SysContext=='layouts')
{
	# THIS IS A LAYOUT PAGE
	$_ENVIRONMENT='f';

	$squery="SELECT P.JSBPageID, P.Caption AS Caption, P.Title AS Title,P.ParentID,
  P.UpdatedAt AS UpdatedAt, P.Options AS Options, P.State AS State, P.MetaData AS Metadata
  FROM jsb_Pages AS P
  WHERE P.SysContext='layouts' AND P.JSBPageID=$JSBPageID";
	$qp=DBQuery ($squery);
	if ($qp)
	{
		$JSB_PageData=$qp->Top;
		parse_str ($JSB_PageData->Options,$Options);
		$JSB_PageData->TemplateFileName=basename($Options['phtmpl']);
		$JSB_PageData->Literal=$Options['lit'];
		$JSB_PageData->ObjectClass=$Options['obj'];
		$JSB_PageData->SysContext='layouts';
		if (!$JSB_PageData->TemplateFileName)	{
			print_error("No PHTML file defined in Layout","LayoutPageID:$JSBPageID");
			exit;
		}
	}
	else{
		print_error("Layout not found","$SysContext/$JSBPageID");
	}
}
else
{
	# NOT LAYOUT

	if (!$SysContext){
		print_error("No SysContext to display by pageloader");
		exit;
	}

	$squery="SELECT ctx.SysContext, ctx.ObjectClass, P.ParentID, P.JSBPageID, P.Caption AS Caption, P.Title AS Title,P.ParentID,
  P.UpdatedAt AS UpdatedAt, P.Options AS Options, P.State AS State, P.MetaData AS Metadata,
  L.JSBPageID AS LPageID, L.Caption AS LCaption, L.Options AS LOptions, L.MetaData AS LMetadata
  FROM jsb_Pages AS P, sys_Contexts AS ctx, jsb_Pages AS L
  WHERE P.SysContext = ctx.SysContext AND L.JSBPageID=P.JSBLayoutID AND L.SysContext='layouts' AND ";
	$s="P.SysContext='$SysContext' AND P.JSBPageID=".(($JSBPageID)? $JSBPageID : "ctx.HomePageID");

	$qp=DBQuery ($squery.$s);
	if ($qp)
	{
		$JSBPageID=$qp->Top->JSBPageID;
		parse_str($qp->Top->Options,$Options);
		$v=$Options['virtual'];
		if ($v)
		{
			list ($SysContext,$JSBPageID)=explode ('/',$v);
			$qp=DBQuery ($squery."P.SysContext='$SysContext' AND P.JSBPageID=$JSBPageID");
		}
		$JSB_PageData=$qp->Top;
	}

	if ((!$qp)&&($JSBPageID==0)){
		print_error("Context has no homepage but homepage requested",$SysContext);
		exit;
	}

	if ((!$qp)&&($JSBPageID!=0)){
		# page not present in database. it is dynamic page!
		#
		# read layout data
		$squery="SELECT ctx.SysContext, ctx.ObjectClass,
    L.JSBPageID AS LPageID, L.Caption AS LCaption, L.Options AS LOptions, L.MetaData AS LMetadata
    FROM jsb_Pages AS P, sys_Contexts AS ctx, jsb_Pages AS L
    WHERE ctx.SysContext=P.SysContext AND P.SysContext='$SysContext' AND P.JSBPageID=0 AND L.JSBPageID=P.JSBLayoutID AND L.SysContext='layouts'";
		$qp=DBQuery($squery);
		if (!$qp) {
			print_error ($__['ERROR_NO_CONTEXT_OR_LAYOUT'],$SysContext);
			exit;
		}
		if ($JSBPageID!=0) {
			$qp->Top->PageNotRegistered=true;
			$qp->Top->JSBPageID=$JSBPageID;
		}else{
			$JSBPageID=$qp->Top->JSBPageID;
		}
		$JSB_PageData=$qp->Top;
	}

	if ($JSB_PageData->LPageID){
		$JSBLayoutID=$JSB_PageData->LPageID;
		parse_str ($JSB_PageData->LOptions,$LOptions);
		$JSB_PageData->ObjectClass=$LOptions['obj'];
		$JSB_PageData->TemplateFileName=basename($LOptions['phtmpl']);
		$JSB_PageData->Literal=$LOptions['lit'];
	}

	if (!$JSB_PageData->Metadata)$JSB_PageData->Metadata=$JSB_PageData->LMetadata;
	if (!$JSB_PageData->TemplateFileName){
		print_error("No PHTML file defined in Layout","PageID:$JSB_PageData->JSBPageID LayoutPageID:$JSB_PageData->LPageID");
		exit;
	}
	#  if ($JSB_PageData->ObjectClass !=
}

?>
