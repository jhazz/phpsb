<?php
if ($SysContext=='layouts')
  {
  # THIS IS A LAYOUT PAGE
  $_ENVIRONMENT='f';
  $squery="SELECT P.SysContext,ctx.Caption AS ContextCaption, P.JSBPageID, P.Caption AS Caption,
P.Title AS Title, P.UpdatedAt AS PUpdatedAt, P.Options AS Options,
P.State AS State, P.MetaData AS Metadata
FROM sys_Contexts AS ctx RIGHT JOIN jsb_Pages AS P ON ctx.SysContext = P.SysContext
WHERE P.SysContext='layouts' AND P.JSBPageID=$JSBPageID";
  $qp=DBQuery ($squery);
  if ($qp)
    {
    $JSB_PageData=$qp->Top;
    parse_str ($JSB_PageData->Options,$Options);
    $JSB_PageData->TemplateFileName=basename($Options['phtmpl']);
    $JSB_PageData->Literal=$Options['lit'];
    $JSB_PageData->ObjectClass=$Options['obj'];
    if (!$JSB_PageData->TemplateFileName)
      {
      print_error("No PHTML file defined in Layout","LayoutPageID:$JSBPageID");
      exit;
      }
    }
  else
    {
    print_error("Layout not found","$SysContext/$JSBPageID");
    }
  }
else
  {
  # NOT LAYOUT
  if ($JSBPageID!=0)
    {
    # ordinal site page
    $squery="SELECT 1,ctx.SysContext, ctx.ObjectClass, P.JSBPageID, P.Caption AS Caption, P.Title AS Title,P.ParentID,
  P.UpdatedAt AS UpdatedAt, P.Options AS Options, P.State AS State, P.MetaData AS Metadata,
  L.JSBPageID AS LPageID, L.Caption AS LCaption, L.Options AS LOptions, L.MetaData AS LMetadata,
  DL.JSBPageID AS DLPageID, DL.Caption AS DLCaption, DL.Options AS DLOptions, DL.MetaData AS DLMetadata
  FROM (((jsb_Pages AS L RIGHT JOIN jsb_Pages AS P ON L.JSBPageID = P.JSBLayoutID)
  LEFT JOIN sys_Contexts AS ctx ON P.SysContext = ctx.SysContext)
  LEFT JOIN jsb_Pages AS DP ON P.SysContext = DP.SysContext)
  LEFT JOIN jsb_Pages AS DL ON DP.JSBLayoutID = DL.JSBPageID
  WHERE L.SysContext='layouts' AND DL.SysContext='layouts' AND DP.JSBPageID=0 AND ";
    $qp=DBQuery ($squery." P.SysContext='$SysContext' AND P.JSBPageID=$JSBPageID");
    }

  if ((!$qp)||($JSBPageID==0))
    {
    # page not present in database. may be it is dynamic page or homepage ($JSBPageID==0)
    #
    $s="SELECT ctx.SysContext, ctx.ObjectClass,
P.Options, L.Options AS LOptions, P.Title, P.Caption, P.JSBPageID, P.MetaData,P.ParentID,
L.MetaData AS LMetaData, L.Caption AS LCaption,L.JSBPageID AS LPageID,
DL.JSBPageID AS DLPageID, DL.Caption AS DLCaption, DL.Options AS DLOptions, DL.MetaData AS DLMetadata
FROM ((sys_Contexts AS ctx LEFT JOIN (jsb_Pages AS DP LEFT JOIN jsb_Pages AS DL
  ON DP.JSBLayoutID = DL.JSBPageID)
  ON ctx.SysContext = DP.SysContext) LEFT JOIN jsb_Pages AS P
  ON (ctx.SysContext = P.SysContext) AND (ctx.HomePageID = P.JSBPageID))
  LEFT JOIN jsb_Pages AS L ON P.JSBLayoutID = L.JSBPageID
WHERE ctx.SysContext='$SysContext' AND L.SysContext='layouts' AND DP.JSBPageID=0 AND DL.SysContext='layouts'
";
    $qp=DBQuery($s);
    if (!$qp)
      {
      # Error anyway. But we should say what a problem happens
      $qe=DBQuery("SELECT SysContext FROM sys_Contexts WHERE SysContext='$SysContext'");
      if (!$qe)
        {
        print_error ("Undefined context",$SysContext);
        }
      else
        {
        $qe=DBQuery("SELECT JSBLayoutID FROM jsb_Pages WHERE SysContext='$SysContext' AND JSBPageID=0");
        if (!$qe)
          {
          print_error ("Context has no default page with ID=0",$SysContext);
          }
        else
          {
          $DefaultLayoutID=$qe->Top->JSBLayoutID;
          $qe=DBQuery("SELECT JSBPageID FROM jsb_Pages WHERE JSBLayoutID=$DefaultLayoutID AND SysContext='layouts'");
          if (!$qe)
            {
            print_error ("Default page for context has illegal layout. Update layout for context and it will be fixed",$SysContext);
            }
          else
            {
            print_error ("Can't acquire default page information for context",$SysContext);
            }
          }


        }
      exit;
      }

    if ((!$qp->Top->JSBPageID)&&($JSBPageID==0)) # no home page for this context but home page requested
      {
      print_error("Context has no homepage but homepage requested",$SysContext);
      exit;
      }
    if ($JSBPageID!=0)
      {
      $qp->Top->PageNotRegistered=true;
      $qp->Top->JSBPageID=$JSBPageID;
      }
    else
      {
      $JSBPageID=$qp->Top->JSBPageID;
      }
    }

  if ($qp)
    {
    $JSB_PageData=$qp->Top;
    parse_str($JSB_PageData->Options,$Options);
    $v=$Options['virtual'];
    if ($v)
      {
      list ($SysContext,$JSBPageID)=explode ('/',$v);
      $qp=DBQuery ($squery."P.SysContext='$SysContext' AND P.JSBPageID=$JSBPageID");
      if (!$qp)
        {
        print_error("Substituted page not found","$SysContext/$JSBPageID");
        }
      $JSB_PageData=$qp->Top;
      parse_str ($JSB_PageData->Options,$Options);
      }
    $ContextInterfaceName=$JSB_PageData->ContextInterface;
    if ($ContextInterfaceName)
      {
      trace("Loading interface: '$ContextInterface' and execute OnPageLoad(PageData,$_DESIGN_MODE)" );
      $ContextInterface=&$_ENV->LoadInterface($ContextInterfaceName);

      if (!is_object($ContextInterface))
        {
        print_developer_warning ("Error loading context interface for '$SysContext'",$OnPageLoadInterface);
        exit;
        }
      if (method_exists($ContextInterface,"OnPageLoad"))
        {
        $r=$ContextInterface->OnPageLoad($JSB_PageData,$_DESIGN_MODE);
        if ($r['AllowedLoaderClass']) $JSB_AllowedLoaderClass=$r['AllowedLoaderClass'];
        if ($r['HTTPError'])
          {
          print "<script>location.href='$cfg[RootURL]/$_FRONTDOOR/errors/$r[HTTPError].html?from=$_SERVER[REQUEST_URI]';</script>";
          exit;
          }
        if ($r['Error'])
          {
          print_error($r['Error'],$r['Details'],1,$ContextInterfaceName,2);
          exit;
          }
        }

      } else {trace ("Context interface not found '$ContextInterfaceName'",2);}
    }
  else
    {
    print "Page not found";
    exit;
    }

  if ($JSB_PageData->LPageID)
    {
    $JSBLayoutID=$JSB_PageData->LPageID;
    parse_str ($JSB_PageData->LOptions,$LOptions);
    $JSB_PageData->ObjectClass=$LOptions['obj'];
    $JSB_PageData->TemplateFileName=basename($LOptions['phtmpl']);
    $JSB_PageData->Literal=$LOptions['lit'];
    }
  elseif ($JSB_PageData->DLPageID)
    {
    $JSBLayoutID=$JSB_PageData->DLPageID;
    $JSB_PageData->LOptions=$JSB_PageData->DLOptions;
    parse_str ($JSB_PageData->LOptions,$LOptions);
    $JSB_PageData->ObjectClass=$LOptions['obj'];
    $JSB_PageData->TemplateFileName=basename($LOptions['phtmpl']);
    $JSB_PageData->Literal==$LOptions['lit'];
    $JSB_PageData->LCaption=$JSB_PageData->DLCaption;
    $JSB_PageData->LPageID=$JSB_PageData->DLPageID;
    }
  if (!$JSB_PageData->Metadata)$JSB_PageData->Metadata=$JSB_PageData->LMetadata;
  if (!$JSB_PageData->Metadata)$JSB_PageData->Metadata=$JSB_PageData->DLMetadata;
  if (!$JSB_PageData->TemplateFileName)
    {
    print_error("No PHTML file defined in Layout","PageID:$JSB_PageData->JSBPageID LayoutPageID:$JSB_PageData->LPageID");
    exit;
    }
  }

?>
