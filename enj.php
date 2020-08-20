<?php
define("Dbug_", "");

include 'dbenj.php';
include 'uquery.php';
include 'ufenj.php';

  $q =NULL;
  $up =NULL;
  $un=NULL;
  $buf=NULL;
  $insertstr=NULL;
  $patternbuf=NULL;
  $newuser=NULL;
  $newpass=NULL;
  $filter=NULL;
//
  $cappa=NULL;
  $capfname=NULL;

  $vc = 0;
  $ctsk = 0;
  $i = $j = $id = 0;
  $step = $start = $prestep = 0;

  $totalanns = 0;

  $an1 = new tAnn();

  $newupd = new tPrivateData();

if (defined("Dbug")) {
  $f = NULL;
}
//-----------------------------------------------------------

if (defined("Dbug")) {
    $f = fopen("time.txt", "wb");
    fprintf($f, Now());
};

    dbInit();
if (defined("Dbug")) {
    fprintf($f, Now());
};
    $ctsk = DefineTask();
if (defined("Dbug")) {
    fprintf($f, Now());
};
    TestSID();
	
    $buf = ReadPattern("main.html");
    switch ($ctsk) {
      case tskLogon: {
                       $un = QueryValue("login");
                       $up = QueryValue("pass");
                       $i = dbCheckUser($un, $up);
                       dbUnLogon(GetClientIP());
                       switch ($i) {
                         case dbUserNotFound: {
                           $buf = Insert(csLogonResult_UserNotFound, $buf, sPos(csLogonResult, $buf));
                           break; }
                         case dbUserDeleted: {
                           $buf = Insert(csLogonResult_UserDeleted, $buf, sPos(csLogonResult, $buf));
                           break; }
                         case dbUserWrongPass: {
                           $buf = Insert(csLogonResult_UserWrongPass, $buf, sPos(csLogonResult, $buf));
                           break; }
                         case dbUserAccess: {
                           if (dbUserLogon($un, GetClientIP()) == dbOk) {
                             dbReadPrivateData($un, $upd);
                             $buf = Insert(csLogonResult_UserAccess, $buf, sPos(csLogonResult, $buf));
                             $buf = Insert(csLogonResult_UserAccess2 . "$upd->name.</p>", $buf, sPos(csDataTag, $buf));
                           } else
                             $buf = Insert(csLogonResult_AccessError, $buf, sPos(csLogonResult, $buf));
                           break; }
                       }
                       echo $buf;

                     break; };
      case tskAnns:  {
                 $patternbuf = ReadPattern("ann.htm");
                       $totalanns = (int)dbTotalUnDeletedAnnCount();
                       $start = (int)(QueryValue("start"));
                       $step = (int)(QueryValue("step"));
                       $prestep = $step;
                       if ($start > $totalanns) {
                         echo("Illegal start position.");

                         dbClose(mysql);
                         return 0;
                       }
                       if ($start < 1) $start = 1;
                       if (($start+$step) > $totalanns + 1)
                         $step = $totalanns-$start+1;
                       $filter = NavigationBar($start, $step, $totalanns);
                       $buf = Insert($filter, $buf, sPos(csDataTag, $buf));
                       $insertstr = "";
                       for ($j = $start; $j < $start+$step; $j++) {
                         dbReadAnns($an1, $j);
                         $insertstr .= FillPattern($patternbuf, $an1);
                       }
                       $insertstr .= "<p class=\"text\">";
                       if ($start-$prestep < 1) {
                         $insertstr .= "<a href=\"cgi-bin/enj.php?task=anns&start=1&step=";
                         $insertstr .= $prestep;
                         $insertstr .= "\" target=\"_self\">  << Назад         </a>&nbsp;&nbsp;&nbsp;";
                       }
                       else {
                         $insertstr .= "<a href=\"cgi-bin/enj.php?task=anns&start=";
                         $insertstr .= ($start-$prestep);
                         $insertstr .= "&step=";
                         $insertstr .= ($prestep);
                         $insertstr .= "\" target=\"_self\">  << Назад         </a>&nbsp;&nbsp;&nbsp;";
                       }
                       if ($start+$step-1 < $totalanns) {
                         $insertstr .= "<a href=\"cgi-bin/enj.php?task=anns&start=";
                         $insertstr .= ($start+$prestep);
                         $insertstr .= "&step=";
                         $insertstr .= ($prestep);
                         $insertstr .= "\" target=\"_self\"> Вперед >>  </a>";
                       }
                       $insertstr .= "</p>";
                       $buf = Insert($insertstr, $buf, sPos(csDataTag, $buf));
                       echo $buf;
                     break; };
      case tskDetail: {
                       $patternbuf = ReadPattern("detail.htm");

                       dbReadAnnId($an1, (int)QueryValue("id"));
                       $insertstr = FillPattern($patternbuf, $an1);
                       $buf = Insert($insertstr, $buf, sPos(csDataTag, $buf));

                       dbReadPrivateData($an1->user, $newupd);
                       $buf = ReplaceStr($buf, "[phone]", $newupd->phone);
                       echo $buf;
                     break; };
      case tskReg:   {
                       $patternbuf = ReadPattern("registration.htm");
                       dbReadCappa(GetClientIP(), $cappa, $capfname);
if (defined("Dbug")) {
                       fwrite($f, "cappa=$cappa\r\n");
}
if (!defined("Dbug")) {
                       DelCappa($capfname);
                       dbDelCappa(GetClientIP());
}
                       if (strcmp($cappa, QueryValue("cappa")))  {
                         $patternbuf = ReplaceStr($patternbuf, csIllegalReg, csWrongCappa);
                         NewCappa($cappa, $capfname);
                         dbWriteCappa(GetClientIP(), $cappa, $capfname);
                         $patternbuf = ReplaceStr($patternbuf, "[cappaimg]", $capfname);
                       } else {
                         $newuser = QueryValue("user");
                         $newpass = QueryValue("pass");

                         $newupd->name = QueryValue("name");
                         $newupd->phone = QueryValue("phone");
                         $newupd->email = QueryValue("email");
                         try {
                           $newupd->sex = (int)(QueryValue("sex"));
                         }
                         catch(Exception $e) {
                           $newupd->sex = 1;
                         }
                         try {
                           $newupd->age = (int)(QueryValue("age"));
                         }
                         catch(Exception $e) {
                           $newupd->age = 0;
                         }
                         $newupd->icq = QueryValue("icq");
                         $newupd->user = $newuser;
                         $newupd->regdate = Now();
                         if (dbCheckUser($newuser, $newpass) != dbUserNotFound)  {
                           NewCappa($cappa, $capfname);
                           dbWriteCappa(GetClientIP(), $cappa, $capfname);
                           $patternbuf = ReplaceStr($patternbuf, "[cappaimg]", $capfname);
                           $patternbuf = ReplaceStr($patternbuf, csIllegalReg, ReplaceStr(csIllegalRegStr, "[name]", $newuser));
                         } else {
                           $i = dbNewUser($newuser, $newpass);
                           if ($i != dbOk)
                             $patternbuf = ReplaceStr($patternbuf, csIllegalReg, csRegError);
                           else {
                             $i = dbWritePrivateData($newuser, $newupd);
                             if ($i != dbOk)
                               $patternbuf = ReplaceStr($patternbuf, csIllegalReg, csRegError);
                             else {
                               $patternbuf = ReadPattern("regend.htm");
                               if (dbUserLogon($newuser, GetClientIP()) == dbOk)
                                 $patternbuf = Insert(csLogonResult_UserAccess, $patternbuf, sPos(csLogonResult, $patternbuf));
                               else
                                 $patternbuf = Insert(csLogonResult_AccessError, $patternbuf, sPos(csLogonResult, $patternbuf));
                             }
                           }
                         }
                       }
                       echo($patternbuf);
                     break; }

    case tskMyAnns: {
                       $un = dbValidateUserIP(GetClientIP());
                       if (strlen($un))  {
                         $totalanns = (int)dbAnnCount($un);
                         if (!$totalanns) {
                           $buf = Insert(csNoAnns, $buf, sPos(csDataTag, $buf));
                         }

                         $patternbuf = ReadPattern("ann.htm");

                         $patternbuf = ReplaceStr($patternbuf, csCommStart, "");
                         $patternbuf = ReplaceStr($patternbuf, csCommEnd, "");
                         $insertstr = "";
                         for ($j = 1; $j <= $totalanns; $j++) {
                           dbReadAnn($un, $an1, $j);
                           $insertstr .= FillPattern($patternbuf, $an1);
                         }
                         $buf = Insert($insertstr, $buf, sPos(csDataTag, $buf));
                         echo $buf;
                       } else {
                         echo(ReadPattern("plsreg.html"));
                       }
                     break; }
    case tskDelete:  {
                       $un = dbValidateUserIP(GetClientIP());
                       if (strlen($un))  {
                         $id = (int)(QueryValue("id"));
                         if (dbUserAnn($un, $id) == dbOk) {
                           dbReadAnnId($an1, $id);
                           if (dbdeleteann($id) != dbOk)  {
                             $buf = Insert(csDeleteError, $buf, sPos(csDataTag, $buf));
                           } else {
                             $buf = Insert(csDeleteOk, $buf, sPos(csDataTag, $buf));
                             if (strlen($an1->foto1))
                                @unlink(csFileStore . $an1->foto1);
                             if (strlen($an1->foto2))
                                @unlink(csFileStore . $an1->foto2);
                             if (strlen($an1->foto3))
                                @unlink(csFileStore . $an1->foto3);
                             if (strlen($an1->foto4))
                                @unlink(csFileStore . $an1->foto4);
                         }
                         echo $buf;
                         }
                       }
                     break; }

     case tskNewAnn: {
                       $un = dbValidateUserIP(GetClientIP());
                       if (strlen($un))  {
                         dbReadCappa(GetClientIP(), $cappa, $capfname);
                         DelCappa($capfname);
                         dbDelCappa(GetClientIP());
                         if (strcmp($cappa, QueryValue("cappa")))  {
                           $patternbuf = ReadPattern("newann.html");
                           $patternbuf = ReplaceStr($patternbuf, csIllegalReg, csWrongCappa);
                           NewCappa($cappa, $capfname);
                           dbWriteCappa(GetClientIP(), $cappa, $capfname);
                           $patternbuf = ReplaceStr($patternbuf, "[cappaimg]", $capfname);
                           echo($patternbuf);
                         } else {
                           $an1->brand = strtolower(QueryValue("brand"));
                           $an1->brand{0} = strtoupper($an1->brand{0});
                           $an1->model = QueryValue("model");
                           $an1->description = QueryValue("description");
                           $an1->equipment = QueryValue("equipment");
                           try {
                             $an1->price = (float)(QueryValue("price"));
                           }
                           catch(Exception $e) {
                             $an1->price = 0;
                           }
                           $an1->other = QueryValue("other");
                           $an1->date = Now();
                           $an1->user = $un;
                           $an1->deleted = 0;

                           FileProcessor($an1->foto1, 'foto1');
                           FileProcessor($an1->foto2, 'foto2');
                           FileProcessor($an1->foto3, 'foto3');
                           FileProcessor($an1->foto4, 'foto4');

                           dbCreateAnn($un, $an1);

                           echo(ReplaceStr($buf, csDataTag, csNewAnnAddOk));
                         }
                       } else {
                         echo(ReadPattern("plsreg.html"));
                       }

                     break; }
     case tskSearch: {
                       $filter = dbCreateFilter();
                       $totalanns = (int)dbF_AnnCount($filter);

                       $start = (int)(QueryValue("start"));
                       $step = (int)(QueryValue("step"));
                       $prestep = (int)$step;
                       if (($start > $totalanns) && ($totalanns != 0)) {
                         echo("Illegal start position.");

                         dbClose();
                         return 0;
                       }
                       if ($start < 1) $start = 1;
                       if (($start+$step) > $totalanns+1)
                         $step = $totalanns-$start+1;

                       if ($totalanns != 0)
                         $patternbuf = NavigationBar($start, $step, $totalanns);
                       else
                         $patternbuf = ReadPattern("find.html");

                       $patternbuf = ReplaceStr($patternbuf, "Ford", QueryValue("brand"));
                       $patternbuf = ReplaceStr($patternbuf, "C-Max", QueryValue("model"));
                       $patternbuf = ReplaceStr($patternbuf, "200000", QueryValue("price"));
                       $patternbuf = ReplaceStr($patternbuf, "checked ", "");

                       if (!strcmp(QueryValue("direction"), "1"))
                         $patternbuf = Insert("checked ", $patternbuf, sPos(csPriceRadioUp, $patternbuf));
                       else
                         $patternbuf = Insert("checked ", $patternbuf, sPos(csPriceRadioDown, $patternbuf));

                       $buf = Insert($patternbuf, $buf, sPos(csDataTag, $buf));

                       if ($totalanns == 0)  {
                         $buf = ReplaceStr($buf, csDataTag, csNoSearchResult);
                       } else {
                         $patternbuf = ReadPattern("ann.htm");
                         $insertstr = "";
                         for ($j = $start; $j<=$start+$step-1; $j++) {
                           dbF_ReadAnn($filter, $an1, $j);
                           $insertstr .= FillPattern($patternbuf, $an1);
                         }
                         $insertstr .= "<p class=\"text\">";

                         $filter = csSearchGoStr;
if (defined("Dbug")) {
                         fwrite($f, $filter . "\r\n");
}
                         $filter = ReplaceStr($filter, "[brand]", QueryValue("brand"));
                         $filter = ReplaceStr($filter, "[model]", QueryValue("model"));
                         $filter = ReplaceStr($filter, "[direction]", QueryValue("direction"));
                         $filter = ReplaceStr($filter, "[price]", QueryValue("price"));
                         $filter = ReplaceStr($filter, "[newfind]", "0");


                         $filter = ReplaceStr($filter, "[step]", (int)($prestep));

                         $patternbuf = $filter;

                         $filter = ReplaceStr($filter, "[caption]", "  << Назад         ");
                         if ($start-$prestep < 1)
                           $filter = ReplaceStr($filter, "[start]", "1");
                         else
                           $filter = ReplaceStr($filter, "[start]", (int)($start-$prestep));

                         $insertstr .= $filter;
                         $patternbuf = ReplaceStr($patternbuf, "[caption]", " Вперед >>  ");
                         if ($start+$step < $totalanns)  {
                           $patternbuf = ReplaceStr($patternbuf, "[start]", (int)($start+$prestep));
                           $insertstr .= $patternbuf;
                         }
                         $insertstr .= "</p>";
                         $buf = Insert($insertstr, $buf, sPos(csDataTag, $buf));
                       };
                       echo $buf;
                     break; }
     case tskNewReg: {
                       dbReadCappa(GetClientIP(), $cappa, $capfname);
                       if (strlen($capfname))
                         DelCappa($capfname);
                       if (strlen($cappa))
                         dbDelCappa(GetClientIP());

                       $buf = ReadPattern("registration.htm");
                       NewCappa($cappa, $capfname);
                       dbWriteCappa(GetClientIP(), $cappa, $capfname);
                       $buf = ReplaceStr($buf, "[cappaimg]", $capfname);
                       echo $buf;
                     break; }
   case tskPrnewann: {
                       $un = dbValidateUserIP(GetClientIP());
                       if (strlen($un))  {
                         dbReadCappa(GetClientIP(), $cappa, $capfname);
                         if (strlen($capfname))
                           DelCappa($capfname);
                         if (strlen($cappa))
                           dbDelCappa(GetClientIP());

                         $buf = ReadPattern("newann.html");
                         NewCappa($cappa, $capfname);
                         dbWriteCappa(GetClientIP(), $cappa, $capfname);
                         $buf = ReplaceStr($buf, "[cappaimg]", $capfname);
                         echo $buf;
                       } else {
                         echo(ReadPattern("plsreg.html"));
                       }
                     break; }
    };
if (defined("Dbug")) {
    fprintf($f, Now());
}
    dbClose();
if (defined("Dbug")) {
    fprintf($f, Now());
}
if (defined("Dbug")) {
    fprintf($f, Now());
    fclose($f);
}

  chdir("../www");

?>