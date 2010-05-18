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

require_once("include/bittorrent.php");
dbconn();
loggedinorreturn();
if (get_user_class() < UC_MODERATOR)
	stderr($tracker_lang["error"], $tracker_lang["access_denied"]);

$types = array(
	"notemplate" => array("type" => "notemplate", "name" => "��� �������"),
	"video" => array("type" => "video", "name" => "�����"),
	"games" => array("type" => "games", "name" => "����"),
	"music" => array("type" => "music", "name" => "������"),
	"soft" => array("type" => "soft", "name" => "���������"),
);

$templates = array(
	"notemplate" => array("toptemplate" => "", "centertemplate" => "", "bottomtemplate" => ""),
	"video" => array("toptemplate" => "[b]����:[/b] \n[b]��������:[/b] \n[b]� �����:[/b] ", "centertemplate" => "[b]� ������:[/b] ", "bottomtemplate" => "[b]��������:[/b] \n[b]�����:[/b] \n[b]�����:[/b] \n[b]�����������������:[/b] \n[b]����:[/b] \n[b]�������:[/b] "),
	"music" => array("toptemplate" => "[b]�����������:[/b] \n[b]������:[/b] \n[b]��� �������:[/b] \n[b]�����:[/b] ", "centertemplate" => "[b]��������:[/b] ", "bottomtemplate" => "[b]����:[/b] \n[b]�����������������:[/b] "),
	"games" => array("toptemplate" => "[b]��������:[/b] \n[b]�������������:[/b] \n[b]����:[/b] \n[b]��� �������:[/b] ", "centertemplate" => "[b]��������:[/b] ", "bottomtemplate" => "[b]��������� ����������:[/b] \n[b]������:[/b] "),
	"soft" => array("toptemplate" => "[b]��������:[/b] \n[b]�������������:[/b] \n[b]��� �������:[/b] ", "centertemplate" => "[b]��������:[/b] ", "bottomtemplate" => "[b]��������� ����������:[/b] "),
);

if (empty($_GET["type"])) {
stdhead("�������� ��� ������");
?>
<form action="indexadd.php" method="get">
	<table border="1" cellspacing="0" cellpadding="3" width="20%">
	<tr><td class="heading" align="right">���</td><td>
	<select name="type">
<?
	foreach ($types as $type)
		print("<option value=\"" . $type["type"] . "\">" . $type["name"] . "</option>");
?>
	</select>
	</td></tr>
	<tr><td align="center" colspan="2"><input type="submit" class=btn value="������"></td></tr>
	</table>
</form>
<?
stdfoot();
die;
} else
	$type = $_GET["type"];

stdhead("�������� ����� - ".$types[$type]["name"]);

$cats = genrelist();
$categories = "<select name=\"cat\"><option selected>�������� ���������</option>";
foreach ($cats as $cat) {
	$cat_id = $cat["id"];
	$cat_name = $cat["name"];
	$categories .= "<option value=\"$cat_id\">$cat_name</option>";
}
$categories .= "</select>";

?>

<form name="index" action="takeindex.php" method="post">
<table border="0" cellspacing="0" cellpadding="5">
<tr><td class="colhead" colspan="2">��������� ������: <?=$types[$type]["name"];?></td></tr>
<?
tr("�������� ������", "<input type=\"text\" name=\"name\" size=\"80\" /><br />������: ������ ���������� (2006) DVDRip\n", 1);
tr("������", "<input type=\"text\" name=\"poster\" size=\"80\" /><br />������ �������� �� <a href=\"http://www.imageshack.us\">ImageShack</a>", 1);
?>
<tr><td width="" class="heading" valign="top" align="right">������� ������</td><td valign="top" align="left"><?=textbbcode("index", "top", $templates[$type]["toptemplate"]);?></td></tr>
<tr><td width="" class="heading" valign="top" align="right">������� ������</td><td valign="top" align="left"><?=textbbcode("index", "center", $templates[$type]["centertemplate"]);?></td></tr>
<tr><td width="" class="heading" valign="top" align="right">������ ������</td><td valign="top" align="left"><?=textbbcode("index", "bottom", $templates[$type]["bottomtemplate"]);?></td></tr>
<?
tr("����� ��������", "<input type=\"text\" name=\"torrentid\" size=\"60\" /><br />������: $DEFAULTBASEURL/details.php?id=<b>6764</b><br />���������� ������ - � ���� ����� ��������\n", 1);
tr("URL IMDB", "<input type=\"text\" name=\"imdb\" size=\"60\" /><br />������: http://www.imdb.com/title/tt0408306/\n", 1);
tr("���������", $categories, 1);
?>
<tr><td align="center" colspan="2"><input type="submit" value="��������" /></td></tr>
</table>
</form>

<?
stdfoot();
?>