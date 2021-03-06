<?

/*
// +--------------------------------------------------------------------------+
// | Project:    TBDevYSE - TBDev Yuna Scatari Edition                        |
// +--------------------------------------------------------------------------+
// | This file is part of TBDevYSE. TBDevYSE is based on TBDev,               |
// | originally by RedBeard of TorrentBits, extensively modified by           |
// | Gartenzwerg.                                                             |
// |                                                                          |
// | TBDevYSE is free software; you can redistribute it and/or modify         |
// | it under the terms of the GNU General Public License as published by     |
// | the Free Software Foundation; either version 2 of the License, or        |
// | (at your option) any later version.                                      |
// |                                                                          |
// | TBDevYSE is distributed in the hope that it will be useful,              |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with TBDevYSE; if not, write to the Free Software Foundation,      |
// | Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            |
// +--------------------------------------------------------------------------+
// |                                               Do not remove above lines! |
// +--------------------------------------------------------------------------+
*/

require_once("include/benc.php");
require_once("include/bittorrent.php");

function bark($msg) {
	stderr("Ошибка", $msg);
}

////////////////////////////////////////////////
function uploadimage($x, $imgname, $tid) {

	$maxfilesize = 512000; // 500kb

	$allowed_types = array(
	"image/gif" => "gif",
	"image/pjpeg" => "jpg",
	"image/jpeg" => "jpg",
	"image/jpg" => "jpg",
	"image/png" => "png"
	// Add more types here if you like
	);

	if (!($_FILES[image.$x]['name'] == "")) {

		if ($imgname != "") {
			// Make sure is same as in takeedit.php (except for the $imgname bit)
			$img = "torrents/images/$imgname";
			$del = unlink($img);
		}

		$y = $x + 1;

		// Is valid filetype?
		if (!array_key_exists($_FILES[image.$x]['type'], $allowed_types))
			bark("Invalid file type! Image $y (".htmlspecialchars($_FILES[image.$x]['type']).")");

		if (!preg_match('/^(.+)\.(jpg|jpeg|png|gif)$/si', $_FILES[image.$x]['name']))
			bark("Неверное имя файла (не картинка).");

		// Is within allowed filesize?
		if ($_FILES[image.$x]['size'] > $maxfilesize)
			bark("Invalid file size! Image $y - Must be less than 500kb");

		// Where to upload?
		// Make sure is same as on takeupload.php
		$uploaddir = "torrents/images/";

		// What is the temporary file name?
		$ifile = $_FILES[image.$x]['tmp_name'];

		// By what filename should the tracker associate the image with?
		//$ifilename = $tid . $x . substr($_FILES[image.$x]['name'], strlen($_FILES[image.$x]['name'])-4, 4);
		$ifilename = $tid . $x . '.' . end(explode('.', $_FILES[image.$x]['name']));

		// Upload the file
		$copy = copy($ifile, $uploaddir.$ifilename);

		if (!$copy)
			bark("Error occured uploading image! - Image $y");

		return $ifilename;

	}

}
////////////////////////////////////////////////

function dict_check($d, $s) {
	if ($d["type"] != "dictionary")
		bark("not a dictionary");
	$a = explode(":", $s);
	$dd = $d["value"];
	$ret = array();
	foreach ($a as $k) {
		unset($t);
		if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
			$k = $m[1];
			$t = $m[2];
		}
		if (!isset($dd[$k]))
			bark("dictionary is missing key(s)");
		if (isset($t)) {
			if ($dd[$k]["type"] != $t)
				bark("invalid entry in dictionary");
			$ret[] = $dd[$k]["value"];
		}
		else
			$ret[] = $dd[$k];
	}
	return $ret;
}

function dict_get($d, $k, $t) {
	if ($d["type"] != "dictionary")
		bark("not a dictionary");
	$dd = $d["value"];
	if (!isset($dd[$k]))
		return;
	$v = $dd[$k];
	if ($v["type"] != $t)
		bark("invalid dictionary entry type");
	return $v["value"];
}

dbconn();
loggedinorreturn();

if (!mkglobal("id:name:descr:type"))
	bark("missing form data");

$id = intval($id);
if (!$id)
	die();

$res = sql_query("SELECT owner, filename, save_as, image1, image2, image3, image4, image5 FROM torrents WHERE id = $id");
$row = mysql_fetch_array($res);
if (!$row)
	die();

if ($CURUSER["id"] != $row["owner"] && get_user_class() < UC_MODERATOR)
	bark("You're not the owner! How did that happen?\n");

$updateset = array();

$fname = $row["filename"];
preg_match('/^(.+)\.torrent$/si', $fname, $matches);
$shortfname = $matches[1];
$dname = $row["save_as"];

// picturemod
for ($x=1; $x <= 5; $x++) {
	$_GLOBALS['img'.$x.'action'] = $_POST['img'.$x.'action'];
	if ($_GLOBALS['img'.$x.'action'] == 'update')
		$updateset[] = 'image' . $x . ' = ' .sqlesc(uploadimage($x - 1, $row['image' . $x], $id));
	if ($_GLOBALS['img'.$x.'action'] == 'delete') {
		if ($row['image' . $x]) {
			$del = unlink('torrents/images/' . $row['image' . $x]);
			$updateset[] = 'image' . $x . ' = ""';
		}
	}

	/*$img1action = $_POST['img1action'];
	if ($img1action == "update")
		$updateset[] = "image1 = " .sqlesc(uploadimage(0, $row[image1], $id));
	if ($img1action == "delete") {
		if ($row[image1]) {
			$del = unlink("torrents/images/$row[image1]");
			$updateset[] = "image1 = ''";
		}
	}

	$img2action = $_POST['img2action'];
	if ($img2action == "update")
		$updateset[] = "image2 = " .sqlesc(uploadimage(1, $row[image2], $id));
	if ($img2action == "delete") {
		if ($row[image2]) {
			$del = unlink("torrents/images/$row[image2]");
			$updateset[] = "image2 = ''";
		}
	}*/
}
// picturemod

if (isset($_FILES["tfile"]) && !empty($_FILES["tfile"]["name"]))
	$update_torrent = true;

if ($update_torrent) {
	$f = $_FILES["tfile"];
	$fname = unesc($f["name"]);
	if (empty($fname))
		bark("Файл не загружен. Пустое имя файла!");
	if (!validfilename($fname))
		bark("Неверное имя файла!");
	if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
		bark("Неверное имя файла (не .torrent).");
	$tmpname = $f["tmp_name"];
	if (!is_uploaded_file($tmpname))
		bark("eek");
	if (!filesize($tmpname))
		bark("Пустой файл!");
	$dict = bdec_file($tmpname, $max_torrent_size);
	if (!isset($dict))
		bark("Что за хрень ты загружаешь? Это не бинарно-кодированый файл!");
	list($info) = dict_check($dict, "info");
	list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");
	if (strlen($pieces) % 20 != 0)
		bark("invalid pieces");

	$filelist = array();
	$totallen = dict_get($info, "length", "integer");
	if (isset($totallen)) {
		$filelist[] = array($dname, $totallen);
		$torrent_type = "single";
	} else {
		$flist = dict_get($info, "files", "list");
		if (!isset($flist))
			bark("missing both length and files");
		if (!count($flist))
			bark("no files");
		$totallen = 0;
		foreach ($flist as $fn) {
			list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
			$totallen += $ll;
			$ffa = array();
			foreach ($ff as $ffe) {
				if ($ffe["type"] != "string")
					bark("filename error");
				$ffa[] = $ffe["value"];
			}
			if (!count($ffa))
				bark("filename error");
			$ffe = implode("/", $ffa);
			$filelist[] = array($ffe, $ll);
		if ($ffe == 'Thumbs.db')
	        {
	            stderr("Ошибка", "В торрентах запрещено держать файлы Thumbs.db!");
	            die;
	        }
		}
		$torrent_type = "multi";
	}

	$dict['value']['announce']=bdec(benc_str($announce_urls[0]));  // change announce url to local
	$dict['value']['info']['value']['private']=bdec('i1e');  // add private tracker flag
	$dict['value']['info']['value']['source']=bdec(benc_str( "[$DEFAULTBASEURL] $SITENAME")); // add link for bitcomet users
	unset($dict['value']['announce-list']); // remove multi-tracker capability
	unset($dict['value']['nodes']); // remove cached peers (Bitcomet & Azareus)
	unset($dict['value']['info']['value']['crc32']); // remove crc32
	unset($dict['value']['info']['value']['ed2k']); // remove ed2k
	unset($dict['value']['info']['value']['md5sum']); // remove md5sum
	unset($dict['value']['info']['value']['sha1']); // remove sha1
	unset($dict['value']['info']['value']['tiger']); // remove tiger
	unset($dict['value']['azureus_properties']); // remove azureus properties
	$dict=bdec(benc($dict)); // double up on the becoding solves the occassional misgenerated infohash
	$dict['value']['comment']=bdec(benc_str( "Торрент создан для '$SITENAME'")); // change torrent comment
	$dict['value']['created by']=bdec(benc_str( "$CURUSER[username]")); // change created by
	$dict['value']['publisher']=bdec(benc_str( "$CURUSER[username]")); // change publisher
	$dict['value']['publisher.utf-8']=bdec(benc_str( "$CURUSER[username]")); // change publisher.utf-8
	$dict['value']['publisher-url']=bdec(benc_str( "$DEFAULTBASEURL/userdetails.php?id=$CURUSER[id]")); // change publisher-url
	$dict['value']['publisher-url.utf-8']=bdec(benc_str( "$DEFAULTBASEURL/userdetails.php?id=$CURUSER[id]")); // change publisher-url.utf-8
	list($info) = dict_check($dict, "info");

	$infohash = sha1($info["string"]);
	move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");

	$fp = fopen("$torrent_dir/$id.torrent", "w");
	if ($fp) {
	    @fwrite($fp, benc($dict), strlen(benc($dict)));
	    fclose($fp);
	}

	$updateset[] = "info_hash = " . sqlesc($infohash);
	$updateset[] = "filename = " . sqlesc($fname);
	$updateset[] = "save_as = " . sqlesc($dname);
	$updateset[] = "size = " . sqlesc($totallen);
	$updateset[] = "type = " . sqlesc($torrent_type);
	$updateset[] = "numfiles = " . count($filelist);

	@sql_query("DELETE FROM files WHERE torrent = $id");
	foreach ($filelist as $file) {
		@sql_query("INSERT INTO files (torrent, filename, size) VALUES ($id, ".sqlesc($file[0]).",".$file[1].")");
	}

}

$name = htmlspecialchars($name);

$descr = unesc($_POST["descr"]);
if (!$descr)
	bark("Вы должны ввести описание!");

$updateset[] = "name = " . sqlesc($name);
$updateset[] = "search_text = " . sqlesc(htmlspecialchars("$shortfname $dname $torrent"));
$updateset[] = "descr = " . sqlesc($descr);
$updateset[] = "ori_descr = " . sqlesc($descr);
$updateset[] = "category = " . (0 + $type);
if (get_user_class() >= UC_ADMINISTRATOR) {
	if ($_POST["banned"]) {
		$updateset[] = "banned = 'yes'";
		$_POST["visible"] = 0;
	} else
		$updateset[] = "banned = 'no'";
	if ($_POST["sticky"] == "yes")
	        $updateset[] = "sticky = 'yes'";
	    else
	        $updateset[] = "sticky = 'no'";
}
if(get_user_class() >= UC_ADMINISTRATOR)
       $updateset[] = "free = '".($_POST["free"]==1 ? 'yes' : 'no')."'";

$updateset[] = "visible = '" . ($_POST["visible"] ? "yes" : "no") . "'";

$updateset[] = "moderated = 'yes'";
$updateset[] = "moderatedby = ".sqlesc($CURUSER["id"]);

sql_query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $id") or sqlerr();

write_log("Торрент '$name' был отредактирован пользователем $CURUSER[username]\n","F25B61","torrent");

$returl = "details.php?id=$id";
if (isset($_POST["returnto"]))
	$returl .= "&returnto=" . urlencode($_POST["returnto"]);

header("Refresh: 0; url=$returl");

?>