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

require "include/bittorrent.php";
dbconn();

$id = intval($_GET["id"]);
if (!$id)
	stderr($tracker_lang['error'], $tracker_lang['invalid_id']);

$res = sql_query("SELECT username, class, email FROM users WHERE id=$id");
$arr = mysql_fetch_assoc($res) or stderr($tracker_lang['error'], "Нет такого пользователя.");
$username = $arr["username"];
if ($arr["class"] < UC_MODERATOR)
	stderr($tracker_lang['error'], $tracker_lang['access_denied']);

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$to = $arr["email"];

	$from = substr(trim($_POST["from"]), 0, 80);
	if ($from == "") $from = "Анонимно";

	$from_email = substr(trim($_POST["from_email"]), 0, 80);
	if ($from_email == "") $from_email = $SITEEMAIL;
	if (!strpos($from_email, "@")) stderr($tracker_lang['error'], "Введеный e-mail адрес не похож на верный.");

	$from = "$from <$from_email>";

	$subject = substr(trim($_POST["subject"]), 0, 80);
	if ($subject == "") $subject = "(Без темы)";
	$subject = "Fwd: $subject";

	$message = trim($_POST["message"]);
	if ($message == "") stderr($tracker_lang['error'], "Вы не ввели сообщение!");

	$message = "Сообщение отправлено с IP адреса $_SERVER[REMOTE_ADDR] в " . date("Y-m-d H:i:s") . " GMT.\n" .
		"Внимание: Отвечая на это письмо, вы раскроете вам e-mail адрес.\n" .
		"---------------------------------------------------------------------\n\n" .
		$message . "\n\n" .
		"---------------------------------------------------------------------\n$SITENAME E-Mail Шлюз\n";

	$success = @mail($to, $subject, $message, "From: $from", "-f$SITEEMAIL");

	if ($success)
		stderr($tracker_lang['success'], "E-mail успешно отправлен.");
	else
		stderr($tracker_lang['error'], "Письмо не может быть отправлено. Пожалуйста, попробуйте позже.");
}

stdhead("E-mail шлюз");
?>
<table border=1 cellspacing=0 cellpadding=5>
<tr><td class=colhead colspan=2>Отправить e-mail пользователю <?=$username;?></td></tr>
<form method=post action=email-gateway.php?id=<?=$id?>>
<tr><td class=rowhead>Ваше имя</td><td><input type=text name=from size=80></td></tr>
<tr><td class=rowhead>Ваш e-mail</td><td><input type=text name=from_email size=80></td></tr>
<tr><td class=rowhead>Тема</td><td><input type=text name=subject size=80></td></tr>
<tr><td class=rowhead>Сообщение</td><td><textarea name=message cols=80 rows=20></textarea></td></tr>
<tr><td colspan=2 align=center><input type=submit value="Send" class=btn></td></tr>
</form>
</table>
<p>
<font class=small><b>Внимание:</b> Ваш IP-адрес будет записан и будет виден получателю, для предотвращения обмана.<br />
Убедитесь что вы ввели правильный e-mail адрес если вы ожидаете ответа.</font>
</p>
<? stdfoot(); ?>