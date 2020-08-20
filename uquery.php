<?php
define("tsk", "task");
define("tskNoTask", 10000);
define("tskLogon"  , 10001);
define("tskAnns"   , 10002);
define("tskDetail", 10003);
define("tskReg"   , 10004);
define("tskMyAnns", 10005);
define("tskDelete", 10006);
define("tskNewAnn", 10007);
define("tskSearch", 10008);
define("tskNewReg", 10009);
define("tskPrnewann", 10010);


define("csDataTag", "<!--/ Данные /-->");
define("csBlankImg", "blank.jpg");
define("csIllegalReg", "<!--/ [IllegalReg] /-->");
define("csRegError", "<font color=red>Внутрення ошибка при регистрации пользователя. Проверьте все поля и попробуйте ещё раз. При отсутсвии результата свяжитесь с администратом сайта.</font>");
define("csLogonResult", "<!-- [logonresult] //-->");
define("csLogonResult_UserAccess", "Добро пожаловать.");
define("csLogonResult_UserAccess2", "<p class=\"text\">Добро пожаловать, ");
define("csLogonResult_UserNotFound",  "Пользователь не найден.");
define("csLogonResult_UserDeleted", "Пользователь удален администрацией сайта.");
define("csLogonResult_UserWrongPass", "Не верный пароль.");
define("csLogonResult_AccessError", "Внутренняя ошибка при регистрации пользователя. Попробуйте ещё раз. При отсутсвии результата свяжитесь с администратом сайта.");
define("csDeleteError", "<p class=\"text\">Ошибка при удалении объявления. Попробуйте ещё раз. При отсутсвии результата свяжитесь с администратом сайта.</p>");
define("csDeleteOk", "<p class=\"text\">Объявление удалено. Спасибо за использование нашего сайта.</p>");
define("csIllegalRegStr", "<font color=red>Имя пользователя \"[name]\" уже зарегистрировано.</font>");
define("csCommStart", "<!--[del]");
define("csCommEnd", "[del]//-->");
define("csCommStart2", "<!--[comm]");
define("csCommEnd2", "[comm]//-->");
define("csQT", "Content-Disposition: form-data); name=");
define("csQTN", " name=\"");
define("csNewAnnAddOk", "<p class=\"text\">Объявление успешно добавлено. Спасибо за использование нашего сайта.</p>");
define("csStart", "<input name=\"start\" id=\"start\" type=\"hidden\" value=\"1\">");
define("csStep", "<input name=\"step\" id=\"step\" type=\"hidden\" value=\"10\">");
define("csNoSearchResult", "<p class=\"text\">По Вашему запросу ничего не найдено. Попробуйте изменить запрос. Например, набрать \"Рено\" вместо \"Renault\".</p>");
define("csHKey", "{f5078f21-c551-11d3-89b9-0000f81fe221}");
define("csTKey", "{ecabb0ac-7f19-11d2-978e-0000f8757e2a}");
define("csPriceRadioUp", "name=\"direction\" type=\"radio\" id=\"direction\" value=\"1\">Больше");
define("csPriceRadioDown", "name=\"direction\" type=\"radio\" id=\"direction\" value=\"0\">Меньше");
define("csSearchGoStr", "<a href=\"cgi-bin/enj.php?brand=[brand]&model=[model]&direction=[direction]&price=[price]&task=search&start=[start]&step=[step]&newfind=[newfind]\" target=\"_self\">[caption]</a>&nbsp;&nbsp;&nbsp;");
define("csWrongCappa", "<font color=red>Не верно указаны 5 цифр с картинки.</font>");


// define("csLocalPath", "C:\\Inetpub\\wwwroot\\");
define("csLocalPath", "../www/");


define("csNoAnns", "<p class=\"text\">Объявлений нет.</p>");
//******************************************************************************************

$queryType;
$queryVCount;
$h; // global handle

function DefineTask() {
    $r = tskNoTask;
/*    $s = @$_GET[tsk];
    if (strlen($s) == 0)
      $s = @$_POST[tsk];
*/
    $s = QueryValue("task");
    
    if (!strcmp($s, "logon")) $r = tskLogon;
    else
    if (!strcmp($s, "anns"  )) $r = tskAnns;
    else
    if (!strcmp($s, "detail" )) $r = tskDetail;
    else
    if (!strcmp($s, "reg"  )) $r = tskReg;
    else
    if (!strcmp($s, "myanns" )) $r = tskMyAnns;
    else
    if (!strcmp($s, "delete" )) $r = tskDelete;
    else
    if (!strcmp($s, "newann" )) $r = tskNewAnn;
    else
    if (!strcmp($s, "search" )) $r = tskSearch;
    else
    if (!strcmp($s, "newreg" )) $r = tskNewReg;
    else
    if (!strcmp($s, "prnewann" )) $r = tskPrnewann;

    return $r;
}

function QueryValue($name){
  $chset = "qwertyuiop[]\';lkjhgfdsazxcvbnm,./QWERTYUIOPLKJHGFDSAZXCVBNM<>?\"}{\=-|+_`~";
  $s = @$_REQUEST[$name];
  $s = ReplaceStr($s, '<', '&lt;');
  $s = ReplaceStr($s, '>', '&gt;');
  if (!strcmp(strtolower($name), "id")) {
    if (!strpbrk($name, $chset)) {
       return "-1";
    }
  }
  return $s;
}

function ReadPattern($fName){
  $f = @fopen(csLocalPath . $fName, "rb");
  if ($f != false) {
    $s = fread($f, filesize(csLocalPath . $fName));
    fclose($f);
  }
//  chdir("../www");
//  chdir("/var/www/ba2175/veder.net.ru/www");
  return $s;
}

function GetClientIP(){
  return $_SERVER["REMOTE_ADDR"];
}

function FillPattern($pattern, $uann){

  $l = strlen($pattern);
  $s = $pattern;

  $s = str_replace("[code]", $uann->code, $s);

  $s = str_replace("[brand]", $uann->brand, $s);
  $s = str_replace("[model]", $uann->model, $s);
  $s = str_replace("[description]", $uann->description, $s);
  $s = str_replace("[equipment]", $uann->equipment, $s);
  $s = str_replace("[price]", $uann->price, $s);
  $s = str_replace("[other]", $uann->other, $s);
  $s = str_replace("[date]", $uann->date, $s);

    if (strlen($uann->foto1))
      $s = str_replace("[foto1]", $uann->foto1, $s);
    else
      $s = str_replace("[foto1]", csBlankImg, $s);
    if (strlen($uann->foto2))
      $s = str_replace("[foto2]", $uann->foto2, $s);
    else
      $s = str_replace("[foto2]", csBlankImg, $s);
    if (strlen($uann->foto3))
      $s = str_replace("[foto3]", $uann->foto3, $s);
    else
      $s = str_replace("[foto3]", csBlankImg, $s);
    if (strlen($uann->foto4))
      $s = str_replace("[foto4]", $uann->foto4, $s);
    else
      $s = str_replace("[foto4]", csBlankImg, $s);

  return $s;
}

function NewFUID(){
  $s = "";
  for ($i = 0; $i<30; $i++) {
    $s{$i} = (string)rand(0, 9);
  }
  $s{30}='.';
  $s{31}='j';
  $s{32}='p';
  $s{33}='g';

  return implode("", $s);
}

function Insert($source, $dest, $pos){
  if (($pos > -1) && strlen($source)) {
    $t = strlen($source);
    $l = strlen($dest);
    $l2 = $l + $t;
    $c = "";
    $c = substr($dest, 0, $pos);
    $c .= substr($source, 0, $t);
    $c .= substr($dest, $pos, $l-$pos);
    return $c;
  } else {
    return $dest;
  }
}

function sPos($substr, $str) {
  return strpos($str, $substr);
}

function dbCreateFilter() {
  $filter = dbstrUndelAllAnns;

  $s = strtolower(QueryValue("brand"));
  if (strlen($s)) {
    $s{0} = strtoupper($s{0});
    
   $d = ") AND ((anns.brand) = \"" . $s . "\"";
   $filter = Insert($d, $filter, strpos(dbstrUndelAllAnns, "))"));

  }
//  $s = strtolower(QueryValue("model"));
  $s = QueryValue("model");
// TODO непонятно какой регистр.
  
  if (strlen($s)) {
//    $s{0} = strtoupper($s{0});
    $d = ") AND ((anns.model) = \"" . $s . "\"";
    $filter = Insert($d, $filter, sPos("))", dbstrUndelAllAnns));
  }

  $s = strtolower(QueryValue("price"));
  if (strlen($s)) {
//    try {
      $price = (float)$s;
//    } catch(Exception $e) {
//      $price = 0.0;
//    }
    $s = strtolower(QueryValue("direction"));
    if (!strcmp($s, "1")) {
      $d = ") AND ((anns.price) >= " . $price;
    } else {
      $d = ") AND ((anns.price) <= " . $price;
    }
    $filter = Insert($d, $filter, sPos("))", dbstrUndelAllAnns));
  }

   return $filter;
}

function ReplaceStr($dest, $from, $to){
  return str_replace($from, $to, $dest);
}

function NavigationBar($start, $step, $totalanns) {
  $r = ReadPattern("find.html");
  $r = ReplaceStr($r, csCommStart2, "");
  $r = ReplaceStr($r, csCommEnd2, "");
  $r = ReplaceStr($r, "[start]", $start);
  $r = ReplaceStr($r, "[step]", $start+$step-1);
  $r = ReplaceStr($r, "[totalanns]", $totalanns);
  return $r;
}

?>