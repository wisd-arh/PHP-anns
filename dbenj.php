<?php
$link = NULL;

class tPrivateData {
	var $code;
	var $name;
	var $phone;
	var $email;
	var $sex;
	var $age;
	var $icq;
	var $user;
	var $regdate;
	var $deletedate;
};


class tAnn{
	var $code;
	var $brand;
	var $model;
	var $description; 
	var $equipment;
	var $price;
	var $other;
	var $foto1;
	var $foto2;
	var $foto3;
	var $foto4;
	var $date;
	var $user;
	var $deleted;
	var $deletedate;
} ;

define("dbNoResult",       -1);
define("dbOk",             100);
define("dbUserAccess",     0);
define("dbUserNotFound",   1);
define("dbUserDeleted",    2);
define("dbUserWrongPass",  3);
define("dbOutOfRange",     4);

define("dbUserTimeToLive",  3600);

define("dbstrAllUsers", "SELECT users.* FROM users;");
define("dbstrAllAnns", "SELECT anns.* FROM anns;");
define("dbstrUndelAllAnns", "SELECT anns.code, anns.brand, anns.model, anns.description, anns.equipment, anns.price, anns.other, anns.foto1, anns.foto2, anns.foto3, anns.foto4, anns.date, anns.user, anns.deleted, anns.deletedate FROM anns WHERE (((anns.deleted)=0)) ORDER BY anns.brand, anns.model;");

function exiterr($exitcode)
{
  global $link; 
  echo "MySQL Error: " . mysql_error($link);
  die($exitcode);
}

function dbInit() { 
    global $link;
	$link = mysql_connect("localhost", "ba2175_guest", "guest")
        or die("Could not connect : " . mysql_error());
//    print "Connected successfully";
    mysql_select_db("ba2175_abase") or die("Could not select database");
	
};

function dbClose() {
   global $link;
   mysql_close($link);
   $link = NULL;
}

function dbNewUser($user, $pass){
	global $link;	 

  $i = dbCheckUser($user, $pass);
  if ($i!=dbUserNotFound) return dbNoResult;
/*
INSERT INTO users ( login, password, deleted ) SELECT "user" AS Выражение1, "pass" AS Выражение2, 0 AS Выражение3;

*/
  $str = "INSERT INTO users ( login, password, deleted ) SELECT \"$user\" AS Выражение1, \"$pass\" AS Выражение2, 0 AS Выражение3;";
  
	if (!mysql_query($str))
		exiterr(1);

  return dbOk;
}

function dbCheckUser($user, $pass){
	global $link;	 

	$str = "SELECT users.login, users.password, users.deleted FROM users WHERE (((users.login)=\"$user\"));";

  $result = mysql_query($str);
  if ($result == NULL)
		exiterr(1);
		
	$i = mysql_num_rows($result);
	if ($i == 0) return dbUserNotFound;

  $row = mysql_fetch_row($result);
	
	$pas = $row[1];
	$u = $row[0];
	$del = $row[2];

	if (strcmp($pass, $pas))
		return dbUserWrongPass;

	if (strcmp($del, "0")) 
		return dbUserDeleted;
	
	return dbUserAccess;
}

function dbNewPass($user, $pass){
  $str = "UPDATE users SET users.password = \"$pass\" WHERE (((users.login)=\"$user\"));";

	if (!mysql_query($str))
		exiterr(1);
  return dbOk;
}

function dbDeleteUser($user) {

  $str =  "UPDATE users SET users.deleted = 1 WHERE (((users.login)=\"$user\"));";

	mysql_query($str)
		or die(exiterr(1));

// Записываем дату удаления


  $str = "UPDATE privatedata SET privatedata.deletedate = \"" . Now() . "\" WHERE (((privatedata.user)=\"$user\"));";

	if (!mysql_query($str))
		exiterr(1);

  return dbOk;
}

function Now() {
 // "02-01-2007 11:24:23"
 // dd-MM-yy hh-mm-ss
  return date("j-n-Y G:i:s");

}

function dbNewPrivateData($user){

  $str = "SELECT privatedata.code, privatedata.name, privatedata.phone, privatedata.email, privatedata.sex, privatedata.age, privatedata.icq, privatedata.regdate, privatedata.deletedate, privatedata.user FROM privatedata WHERE (((privatedata.user)=\"$user\"));";
	if (! ($result = mysql_query($str)))
		exiterr(1);

	$i = mysql_num_rows($result);
  if ($i != 0) {
      return dbNoResult;
  }

  $str = "INSERT INTO privatedata ( user, regdate ) SELECT \"$user\" AS Выражение1, \"" . Now() . "\" AS Выражение2;";
	if (!mysql_query($str))
		exiterr(1);

  return dbOk;
}

function SetCP() {
//  $str = "SET NAMES cp1251;";
    $str = "SET NAMES utf8;";
 	if (!mysql_query($str))
		exiterr(1);
}
function dbReadPrivateData($user, &$upd) {

//    InitUPD(&$upd);

  SetCP();

  $str = "SELECT privatedata.code, privatedata.name, privatedata.phone, privatedata.email, privatedata.sex, privatedata.age, privatedata.icq, privatedata.user, privatedata.regdate, privatedata.deletedate  FROM privatedata WHERE (((privatedata.user)=\"$user\"));";

 	if (!($result = mysql_query($str)))
		exiterr(1);

	$i = mysql_num_rows($result);
  if ($i != 1) {
      return dbNoResult;
  }

  $row = mysql_fetch_row($result);
  $upd->code = $row[0];
  $upd->name = $row[1];
  $upd->phone = $row[2];
  $upd->email = $row[3];
  $upd->sex = $row[4];
  $upd->age = $row[5];
  $upd->icq = $row[6];
  $upd->user = $row[7];
  $upd->regdate = $row[8];
  $upd->deletedate = $row[9];

  return dbOk;
}

function dbWritePrivateData($user, &$upd) {

  dbNewPrivateData($user);

  SetCP();
  $str = "UPDATE privatedata SET privatedata.name = \"$upd->name\",
          privatedata.phone = \"$upd->phone\",
          privatedata.email = \"$upd->email\",
          privatedata.sex = \"$upd->sex\",
          privatedata.age = \"$upd->age\",
          privatedata.icq = \"$upd->icq\",
          privatedata.user = \"$upd->user\",
          privatedata.regdate = \"$upd->regdate\",
          privatedata.deletedate =\"$upd->deletedate\"
          WHERE (((privatedata.user)=\"$user\"));";

	if (!mysql_query($str))
		exiterr(1);
  return dbOk;
}

function dbCreateAnn($user, &$uann) {

  SetCP();

  $str = "INSERT INTO anns ( brand, model, description, equipment, price, other, foto1, foto2, foto3, foto4, date, user, deleted, deletedate ) SELECT \"$uann->brand\"
        AS Выражение1, \"$uann->model\"
        AS Выражение2, \"$uann->description\"
        AS Выражение3, \"$uann->equipment\"
        AS Выражение4, $uann->price AS Выражение5, \"$uann->other\"
        AS Выражение6, \"$uann->foto1\"
        AS Выражение7, \"$uann->foto2\"
        AS Выражение8, \"$uann->foto3\"
        AS Выражение9, \"$uann->foto4\"
        AS Выражение10, \"$uann->date\"
        AS Выражение11, \"$uann->user\"
        AS Выражение12, $uann->deleted AS Выражение13, \"$uann->deletedate\" AS Выражение11;";


	if (!mysql_query($str))
		exiterr(1);

  return dbOk;
}

function dbAnnCount($user) {

  $str = "SELECT anns.code, anns.brand, anns.model,
  anns.description, anns.equipment, anns.price,
  anns.other, anns.foto1, anns.foto2, anns.foto3,
  anns.foto4, anns.date, anns.user, anns.deleted,
  anns.deletedate FROM anns WHERE (((anns.user)=\"$user\") AND
  ((anns.deleted) <> 1));";

	if (!($result = mysql_query($str)))
		exiterr(1);

	return mysql_num_rows($result);
}

function dbTotalAnnCount() {
	if (!($result = mysql_query(dbstrAllAnns)))
		exiterr(1);

	return mysql_num_rows($result);
}

function dbTotalUnDeletedAnnCount() {

	if (!($result = mysql_query(dbstrUndelAllAnns)))
		exiterr(1);

	return mysql_num_rows($result);
}

function dbTotalDeletedAnnCount() {
    return (dbTotalAnnCount() - dbTotalUnDeletedAnnCount());
}

function dbReadAnn($user, &$uann, $n) {

  SetCP();

  $str = "SELECT anns.code, anns.brand, anns.model,
  anns.description, anns.equipment, anns.price,
  anns.other, anns.foto1, anns.foto2, anns.foto3,
  anns.foto4, anns.date, anns.user, anns.deleted,
  anns.deletedate FROM anns
  WHERE (((anns.user)=\"$user\") AND ((anns.deleted) <> 1)) ORDER BY anns.brand, anns.model;";

	if (!($result = mysql_query($str)))
		exiterr(1);

  if ($n > mysql_num_rows($result)) {
        return dbNoResult;
  }
    
	if ($n > 0)
    mysql_data_seek($result, $n-1);

  $row = mysql_fetch_row($result);


    $uann->code = $row[0];
    $uann->brand = $row[1];
    $uann->model = $row[2];
    $uann->description = $row[3];
    $uann->equipment = $row[4];
    $uann->price = $row[5];
    $uann->other = $row[6];
    $uann->foto1 = $row[7];
    $uann->foto2 = $row[8];
    $uann->foto3 = $row[9];
    $uann->foto4 = $row[10];
    $uann->date = $row[11];
    $uann->user = $row[12];
    $uann->deleted = $row[13];
    $uann->deletedate = $row[14];


    return dbOk;
}

function dbUpdateAnn($user, &$uann) {
   SetCP();

   $str = "UPDATE anns SET anns.brand = \"$uann->brand\"
   , anns.model = \"$uann->model\"
   , anns.description = \"$uann->description\"
   , anns.equipment = \"$uann->equipment\"
   , anns.price = $uann->price,
   anns.other = \"$uann->other\"
   , anns.foto1 = \"$uann->foto1\"
   , anns.foto2 = \"$uann->foto2\"
   , anns.foto3 = \"$uann->foto3\"
   , anns.foto4 = \"$uann->foto4\"
   , anns.date = \"$uann->date\"
   , anns.user = \"$uann->user\"
   , anns.deleted = $uann->deleted,
   anns.deletedate = \"$uann->deletedate\"
   WHERE (((anns.code)=\"$uann->code\"));";

	if (!mysql_query($str))
		exiterr(1);

  return dbOk;
}

function dbDeleteAnn($ID){

  $str = "UPDATE anns SET anns.deleted = 1, anns.deletedate = \"" . Now() . "\"
  WHERE (((anns.code)=$ID));";


	if (!mysql_query($str))
		exiterr(1);

  return dbOk;
}

function dbReadAnns(&$uann, $n) {
    SetCP();


	if (!($result = mysql_query(dbstrUndelAllAnns)))
		exiterr(1);

  if ($n > mysql_num_rows($result)) {
        return dbNoResult;
  }
	if ($n > 0)
    mysql_data_seek($result, $n-1);

    $row = mysql_fetch_row($result);


    $uann->code = $row[0];
    $uann->brand = $row[1];
    $uann->model = $row[2];
    $uann->description = $row[3];
    $uann->equipment = $row[4];
    $uann->price = $row[5];
    $uann->other = $row[6];
    $uann->foto1 = $row[7];
    $uann->foto2 = $row[8];
    $uann->foto3 = $row[9];
    $uann->foto4 = $row[10];
    $uann->date = $row[11];
    $uann->user = $row[12];
    $uann->deleted = $row[13];
    $uann->deletedate = $row[14];


    return dbOk;

}

function dbReadAnnId(&$uann, $id){
   SetCP();


   $str = "SELECT anns.code, anns.brand, anns.model,
   anns.description, anns.equipment, anns.price,
   anns.other, anns.foto1, anns.foto2, anns.foto3,
   anns.foto4, anns.date, anns.user, anns.deleted,
   anns.deletedate FROM anns WHERE (((anns.code)=$id));";

	if (!($result = mysql_query($str)))
		exiterr(1);

  $row = mysql_fetch_row($result);

    $uann->code = $row[0];
    $uann->brand = $row[1];
    $uann->model = $row[2];
    $uann->description = $row[3];
    $uann->equipment = $row[4];
    $uann->price = $row[5];
    $uann->other = $row[6];
    $uann->foto1 = $row[7];
    $uann->foto2 = $row[8];
    $uann->foto3 = $row[9];
    $uann->foto4 = $row[10];
    $uann->date = $row[11];
    $uann->user = $row[12];
    $uann->deleted = $row[13];
    $uann->deletedate = $row[14];


    return dbOk;
}

function dbF_AnnCount($filter) {
  SetCP();

	if (!($result = mysql_query($filter)))
		exiterr(1);

	return mysql_num_rows($result);

}

function dbF_ReadAnn($filter, &$uann, $n) {
  SetCP();

	if (!($result = mysql_query($filter)))
		exiterr(1);

  if ($n > mysql_num_rows($result)) {
        return dbNoResult;
  }
	if ($n > 0)
    mysql_data_seek($result, $n-1);

    $row = mysql_fetch_row($result);


    $uann->code = $row[0];
    $uann->brand = $row[1];
    $uann->model = $row[2];
    $uann->description = $row[3];
    $uann->equipment = $row[4];
    $uann->price = $row[5];
    $uann->other = $row[6];
    $uann->foto1 = $row[7];
    $uann->foto2 = $row[8];
    $uann->foto3 = $row[9];
    $uann->foto4 = $row[10];
    $uann->date = $row[11];
    $uann->user = $row[12];
    $uann->deleted = $row[13];
    $uann->deletedate = $row[14];


    return dbOk;
}

function dbUserLogon($user, $IP) {

  SetCP();
  

  $tsid = TestSID();
  if (strlen($tsid)) {
     $str = "DELETE logon FROM logon WHERE (((logon.sid)=\"$tsid\"));";

     if (!mysql_query($str))
      	exiterr(1);
  }

  session_start();
  $tsid = session_id();
  setcookie("sid", $tsid, time()+3600);  /* expire in 1 hour */

  $str = "INSERT INTO logon ( ip, date, user, sid ) SELECT \"$IP\" AS Выражение1, \"" . Now() . "\" AS Выражение2, \"$user\" AS Выражение3, \"$tsid\" AS Выражение4;";


	if (!mysql_query($str))
		exiterr(1);


  return dbOk;

}

function dbValidateUserIP($IP) {

  SetCP();

  $tsid = TestSID();
  
  if (!strlen($tsid))
    return "";
    
  $str = "SELECT logon.ip, logon.date, logon.user
  FROM logon WHERE (((logon.ip)=\"$IP\") and ((logon.sid =\"$tsid\")));";

	if (!($result = mysql_query($str)))
		exiterr(1);

  $i = mysql_num_rows($result);

  $row = mysql_fetch_row($result);


  if ($i == 1) {
     if (dbValidateTimeToLive($row[1]))
        return $row[2];
  }

  return "";
}

function dbValidateTimeToLive($utime) {
// 2007 11 24 23 50 16

   $t2 = getdate();
   $t1 = getdate(strtotime($utime));
   if ($t2["mon"] - $t1["mon"]) return 0;
   if ($t2["year"] - $t1["year"]) return 0;

   $delta = ($t2["mday"] - $t1["mday"])*24*60*60 + ($t2["hours"] - $t1["hours"])*60*60 + ($t2["minutes"] - $t1["minutes"])*60 + ($t2["seconds"] - $t1["seconds"]);

  if ($delta < dbUserTimeToLive)
     return 1;
  else
     return 0;
        

}

function dbReadCappa($IP, &$cappa, &$fname){

  $str = "SELECT cappa.ip, cappa.cappa,
  cappa.fname FROM cappa WHERE (((cappa.ip)=\"$IP\"));";

	if (!($result = mysql_query($str)))
		exiterr(1);

  $i = mysql_num_rows($result);

  if ($i == 0) return dbNoResult;

  $row = mysql_fetch_row($result);

  $cappa = $row[1];
  $fname = $row[2];
}


function dbWriteCappa($IP, $cappa, $fname){

   dbDelCappa($IP);

   $str = "INSERT INTO cappa ( ip, cappa, fname )
   SELECT \"$IP\" AS Выражение1,
   \"$cappa\" AS Выражение2,
   \"$fname\" AS Выражение3;";

	if (!mysql_query($str))
		exiterr(1);


  return dbOk;
}

function dbDelCappa($IP) {

  $str = "DELETE cappa FROM cappa WHERE (((cappa.ip)=\"$IP\"));";

	if (!mysql_query($str))
		exiterr(1);

  return dbOk;
}


function dbUnLogon($IP) {
  $tsid = TestSID();
  $str = "DELETE logon FROM logon WHERE (((logon.ip)=\"$IP\") and ((logon.sid)=\"$tsid\"));";

	if (!mysql_query($str))
		exiterr(1);

  return dbOk;
}

function dbUserAnn($user, $id) {
  SetCP();

  $str = "SELECT anns.code, anns.user FROM anns WHERE ((((anns.code)=$id)) and (((anns.user)=\"$user\")));";

	if (!($result = mysql_query($str)))
		exiterr(1);

  $i = mysql_num_rows($result);
  if ($i == 0) return dbNoResult;
  return dbOk;

}
  
function TestSID() {
  if (strlen($_COOKIE["sid"])) {
    return $_COOKIE["sid"];
  } else {
//    session_start();
//    setcookie("sid", session_id(), time()+3600);  /* expire in 1 hour */
//    return session_id();
    return "";
  }
}

?>