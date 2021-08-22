<?
class store_IPriceList {
	
  var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
  var $CopyrightURL="http://www.jhazz.com/store";
  var $ComponentVersion="1.0";

  function DoAction($args)
    {
    global $_USER;
    extract(param_extract(array(
          check=>"int_checkboxes",
          action=>"string",
          JumpAfterPut=>"string",
          DefaultRequestName=>'string=Request of',
          DefaultSetName=>'string=Configuration #',
      ),$args));

    switch (strtolower($action))
      {
      case 'puttocart':
        $ProdIDs=implode (",",array_keys($check));
        if ($ProdIDs)
          {
          print "Put $ProdIDs<br>";

#          $qcurr=DBQuery("SELECT * FROM store_Currencies WHERE IsDefault=1");
#          $BCdivVCrate=$qcurr->Top->BCdivVCrate;
#          $VCurrencyID=$qcurr->Top->CurrencyID;

          # Acquire checked products from args

          $s="SELECT * FROM store_Products WHERE ProductID IN ($ProdIDs)";
          $qprods=DBQuery($s,"ProductID");

          if ($_SESSION->RequestID)
            {
            # Checking user access to the request data
            $s="SELECT SessionKey,UserID,StateID FROM store_Requests WHERE RequestID=$_SESSION->RequestID";
            $qreq=DBQuery($s);
            print $s;

            if ((!$qreq) || ($qreq->Top->SessionKey!=$_SESSION->SessionKey) || ($qreq->Top->UserID!=$_USER->UserID))
              {
              $_SESSION->RequestID=0;
              }
            else
              {
              $qstate=DBQuery ("SELECT * FROM store_RequestStates WHERE StateID=".$qreq->Top->StateID);
              if (!$qstate)
                {
                return array(Error=>"Unknow product request StateID",Details=>$qreq->Top->StateID);
                }
              if ((!$_USER->HasRole("backend:RequestEdit")) && (!$qstate->Top->Editable)) {$_SESSION->RequestID=0;}
              }
            }

          if (!$_SESSION->RequestID)
            {
            $datestr=date("F j, Y, g:i a");
            $now=time();
            $_SESSION->RequestSetID=0;
            $_SESSION->RequestID=DBGetID('store.Request');
            $s="INSERT INTO store_Requests
              SET RequestID=$_SESSION->RequestID,
              UserID=$_USER->UserID,
              SessionKey='$_SESSION->SessionKey',
              Name='$DefaultRequestName $datestr',
              PostedAt=$now";
            print $s;
            DBExec ($s);
            }

          $_SESSION->RequestSetID=intval($_SESSION->RequestSetID);
          # as default it is zero. And think that request set is only one
          if ($_SESSION->RequestSetID==0)
            {
//            $_SESSION->RequestSetID=get_uid('store.RequestSet');
            $qc=DBQuery ("SELECT MAX(RequestSetID) AS MaxRequestSetID FROM store_RequestSets WHERE RequestID=$_SESSION->RequestID");
            if ($qc) $_SESSION->RequestSetID=intval($qc->Top->MaxRequestSetID);
            $_SESSION->RequestSetID++;

            DBExec ("INSERT INTO store_RequestSets
              SET  RequestID=$_SESSION->RequestID,
                RequestSetID=$_SESSION->RequestSetID,
                Name='$DefaultSetName $_SESSION->RequestSetID',
                SetCount=1");
            }

          foreach ($check as $ProductID=>$tmp)
            {
            $ProductID=intval($ProductID);
            $p=$qprods->Rows[$ProductID];
            if (!$p) {continue;}

            $RowIndex=get_uid('store.Request.RowIndex');

            DBExec ("REPLACE INTO store_RequestDetails
              SET RowIndex=$RowIndex,
                RequestID   =$_SESSION->RequestID,
                RequestSetID=$_SESSION->RequestSetID,
                ProductID=$ProductID,
                BCPrice=$p->BCPrice,
                VCPrice=$p->VCPrice,
                Name='$p->Name',
                SKU='$p->SKU',
                ItemCount=1");
            }

/*          $IShopCart=&$_ENV->LoadInterface("store.IShopCart");
          $res=$IShopCart->Report(array(ShowTable=>true,
            TableStyle=>1,
            RequestSetID=>$_SESSION->RequestSetID,
            RequestID=>$_SESSION->RequestID,
            ));

          if ($res)
            {
//            var_dump($res);
            if ($res['Error']) {print_error($res['Error'],$res['Details'],$res['IntruderAlert']);}
            }
*/

          print "<a href='$JumpAfterPut'>WE ARE GOING HERE: $JumpAfterPut</a>";

//          DBExec ("DELETE FROM msg_Rateit WHERE RateMsgID IN ($s) AND BindTo='$BindTo'");
          }

        break;
      }
    }

  function OpenNewCart()
    {
    $_SESSION->RequestID=0;
    $_SESSION->RequestSetID=0;
    }
  }
