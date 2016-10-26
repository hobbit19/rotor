<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');

$arrfile = array(
	'upload/avatars',
	'upload/counters',
	'upload/events',
	'upload/forum',
	'upload/news',
	'upload/photos',
	'upload/pictures',
	'upload/thumbnail',
	'images/avatars',
	'images/smiles',
	'upload/files',
	'upload/screen',
	'upload/loader',
	'local/antidos',
	'local/backup',
	'local/board',
	'local/main',
	'local/temp'
);

header("Content-type:text/html; charset=utf-8");
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
echo '<title>Обновление RotorCMS</title>';
echo '<link rel="stylesheet" href="style.css" type="text/css" />';
echo '<meta name="generator" content="RotorCMS" />';
echo '</head><body>';

echo '<div class="cs" id="up"><img src="/images/img/logo.png" alt="RotorCMS" /><br />';
echo 'Система управления мобильным сайтом</div><div>';

$act = (isset($_GET['act'])) ? check($_GET['act']) : '';

switch ($act):
############################################################################################
##                                    Принятие соглашения                                 ##
############################################################################################
	case '0':

		echo '<img src="/images/img/setting.png" alt="img" /> <b>ПРИНЯТИЕ СОГЛАШЕНИЯ</b><br /><br />';

		echo '<big><b>Пользовательское соглашение</b></big><br />';

		$agreement = 'Пользовательское соглашение на использование скриптов, распространяемых cайтом VISAVI.NET.
Ограниченное использование
Приобретая лицензию на программный продукт RotorCMS, вы должны знать, что не приобретаете авторские права на программный продукт. Вы приобретаете только право на использование программного продукта на единственном веб сайте (одном домене и его поддоменах), принадлежащем Вам или Вашему клиенту. Для использования скрипта на другом сайте, вам необходимо приобретать повторную лицензию. Запрещается перепродажа скрипта третьим лицам, и если вы приобретаете скрипт для Ваших клиентов, то вы обязаны ознакомить Ваших клиентов с данным лицензионным соглашением. Также в случае приобретения скрипта не для собственного использования, а для установки на сайты Ваших клиентов, мы не несем обязательств по поддержке Ваших клиентов.

Права и обязанности сторон

Пользователь имеет право:
- Изменять дизайн и структуру программного кода в соответствии с нуждами своего сайта.
- Производить и распространять инструкции по созданным Вами модификациям и дополнениям, если в них будет иметься указание на оригинального разработчика программного продукта до Ваших модификаций. Модификации, произведенные Вами самостоятельно, не являются собственностью VISAVI.NET, если не содержат программные коды непосредственно скрипта.
- Создавать модули, которые будут взаимодействовать с нашими программными кодами, с указанием на то, что это Ваш оригинальный продукт.
- Переносить программный продукт на другой сайт после обязательного уведомления нас об этом, а также полного удаления скрипта с предыдущего сайта.

Пользователь не имеет право:
- Передавать права на использование программного продукта третьим лицам.
- Изменять структуру программных кодов, функции программы, с целью создания родственных продуктов
- Создавать отдельные самостоятельные продукты, базирующиеся на нашем программном коде
- Использовать копии программного продукта RotorCMS по одной лицензии на более чем одном сайте (одном домене и его поддоменах)
- Рекламировать, продавать или публиковать на своем сайте пиратские копии нашего программного продукта
- Распространять или содействовать распространению нелицензионных копий программного продукта RotorCMS
- Удалять механизмы проверки наличия оригинальной лицензии на использование скрипта
- Удалять копирайты и другую авторскую информацию со страниц движка

Досрочное расторжение договорных обязательств

Данное соглашение расторгается автоматически, если Вы отказываетесь выполнять условия нашего договора. Данное лицензионное соглашение может быть расторгнуто нами в одностороннем порядке, в случае установления фактов нарушения данного лицензионного соглашения. В случае досрочного расторжения договора Вы обязуетесь удалить все Ваши копии нашего программного продукта в течении 3 рабочих дней, с момента получения соответствующего уведомления.';

		echo '<form action="index.php?act=1" method="post">';
		echo '<textarea cols="100" rows="20" name="msg">'.$agreement.'</textarea><br /><br />';

		echo '<input name="agree" type="checkbox" value="1" /> <b>Я ПРИНИМАЮ УСЛОВИЯ СОГЛАШЕНИЯ</b><br /><br />';
		echo '<input type="submit" value="Продолжить" /></form><hr />';
		echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">Вернуться</a><br />';
	break;

	############################################################################################
	##                                    Проверка системы                                    ##
	############################################################################################
	case '1':

		$agree = (empty($_REQUEST['agree'])) ? 0 : 1;

		if (!empty($agree)) {
			echo '<img src="/images/img/setting.png" alt="img" /> <b>ОБНОВЛЕНИЕ СИСТЕМЫ</b><br /><br />';

			if ($config['rotorversion'] != '4.0.0') {
				if ($config['rotorversion'] >= '3.0.0' && $config['rotorversion'] < '4.0.0') {
					include_once ('upgrade_4.0.0.dat');
				} else {
					echo '<img src="/images/img/error.gif" alt="image" /> <b>Вы не сможете обновить движок</b><br />Ваша версия движка не соответствует нужным требованиям (от 3.0.0 до 4.0.0)<br /><br />';
				}

				echo 'Если обновление прошло успешно, то закройте эту страницу и удалите директорию <b>upgrade</b><br /><br />';
			} else {
				echo '<img src="/images/img/error.gif" alt="image" /> <b>Ваша система не требует обновлений!</b><br /><br />';
			}
		} else {
			echo '<img src="/images/img/setting.png" alt="image" /> <b>ОТКАЗ ПРИНЯТИЯ УСЛОВИЙ СОГЛАШЕНИЯ</b><br /><br />';
			echo 'Вы не можете продолжить установку движка так как отказываетесь принимать условия соглашения<br />';
			echo 'Любое использование нашего движка означает ваше согласие с нашим соглашением<br /><br />';
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php?act=0">Вернуться</a><br />';
	break;

	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
	default:
		echo '<img src="/images/img/setting.png" alt="img" /> <b>Установка скрипта RotorCMS</b><br /><br />';
		echo 'Добро пожаловать в мастер обновления RotorCMS<br />
Данный мастер поможет вам обновить скрипт всего за пару минут<br /><br />
Прежде чем начать обновление убедитесь, что все файлы дистрибутива загружены на сервер, а также выставлены необходимые права доступа для папок и файлов<br /><br />';

		foreach ($arrfile as $file) {
			echo '<img src="/images/img/right.gif" alt="image" /> <b>'.$file.'</b> (chmod ';
			echo (is_file('../'.$file)) ? 666 : 777;
			echo ')<br />';
		}

		echo 'А также всем файлам внутри папки <b>local/main</b> (chmod 666)<br /><br />';

		echo '<span style="color:#ff0000">Внимание:</span> при обновлении движка изменяется структура базы данных, поэтому желательно сделать бэкап базы данных MySQL, после успешного обновления удалите директорию <b>upgrade</b> во избежание повторного изменения базы данных!<br /><br />
Приятной Вам работы<br /><br />';



		echo '<b>Ваша версия движка: '.$config['rotorversion'].'</b><br />';
		echo '<b>Новая версия движка: 4.0.0</b><br /><br />';

		if ($config['rotorversion'] != '4.0.0') {
			if ($config['rotorversion'] >= '3.0.0' && $config['rotorversion'] < '4.0.0') {
				echo '<img src="/images/img/open.gif" alt="image" /> <b><a href="index.php?act=0">ПРИСТУПИТЬ К ОБНОВЛЕНИЮ</a></b><br /><br />';
			} else {
				echo '<img src="/images/img/error.gif" alt="image" /> <b>Вы не сможете обновить движок</b><br />Ваша версия движка не соответствует нужным требованиям (от 3.0.0 до 4.0.0)<br /><br />';
			}
		} else {
			echo '<img src="/images/img/error.gif" alt="image" /> <b>Ваша система не требует обновлений!</b><br /><br />';
		}
	endswitch;

echo '<img src="/images/img/homepage.gif" alt="image" /> <a href="/index.php">На главную</a>';

echo '</div><div class="lol" id="down">';
echo '<p style="text-align:center">';
echo '<a href="http://visavi.net">Powered by RotorCMS</a><br />';
echo '</p>';
echo '</div></body></html>';
?>
