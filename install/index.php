<?php

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

require_once('./config.php');

$total = $db->sdImportFromFile('database.sql');

header('Refresh: 3; url=../signup.php');
echo "��������� ���������! ��������� $total �������� � ��!<br />������ <font color=\"red\">��� ���� ������� ����� install</font>.<br />������ ��� ������������ �� �������� �����������, ��� �� ����� ����������� ������ ����������.<script>alert('�� �������� ������� ����� install ����� ���������!');</script>";

?>