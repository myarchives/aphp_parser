<?php
if (!isset($_COOKIE['cookie1'])) {
	setcookie('cookie1' , 'value');
	echo 'cookie1 set';
} else {
	echo 'cookie1 = ' . $_COOKIE['cookie1'];
}
