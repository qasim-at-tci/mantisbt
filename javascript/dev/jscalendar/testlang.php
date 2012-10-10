<html>

<?php
	// List of language files
	$dir=opendir( getcwd() . '/lang' );
	while( false !== ( $file = readdir($dir) ) ) {
		if( substr($file, 0, 8) == 'calendar' ) {
			$list[] = $file;
		}
	}
	closedir($dir);
	sort($list);

	echo '<pre>';
//	print_r($_POST);
	print_r($list);
	echo '</pre>';

	// Current language
	if( isset( $_POST['prev'] ) ) {
		$lang = $_POST['prev'];
	} elseif( isset( $_POST['next'] ) ) {
		$lang = $_POST['next'];
	} else {
		$lang = @$_GET['lang'];
	}
	if( is_null( $lang ) ) {
		$lang = array_shift( $list );
		$k = -1;
	} else {
		$k = array_search( $lang, $list );
	}

	$prev = @$list[$k - 1];
	if( is_null( $prev ) ) {
		$prev = end( $list );
	}

	$next = @$list[$k + 1];
?>

<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" type="text/css" href="/mantis/dev/javascript/dev/jscalendar/calendar-blue.css">
	<script type="text/javascript" src="/mantis/dev/javascript/dev/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="/mantis/dev/javascript/dev/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="/mantis/dev/javascript/dev/jscalendar/lang/<?php echo $lang; ?>"></script>
	<script type="text/javascript" src="/mantis/dev/javascript/dev/jscalendar/calendar-setup.js"></script>
</head>

<body>

<?php
	$fld="date_$lang";
	$but="b_$lang";
?>
	<form method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" >

		<label>
			<?php echo $lang; ?><br />
			<input type="text"   id="<?php echo $fld; ?>" value="" />
		</label>
		<img src="/mantis/dev/images/calendar-img.gif" id="<?php echo $but; ?>" value="cal" />

		<script type="text/javascript">
			<!--
				Calendar.setup({ inputField : "<?php echo $fld; ?>", ifFormat : "%Y-%m-%d", button : "<?php echo $but; ?>",
					align : "cR", singleClick : false,  showTime : false, firstDay: 1 });
			//-->
		</script>
		<br />

		<input type="submit" value="<?php echo $prev; ?>" name="prev" />
		<input type="submit" value="<?php echo $next ? $next : $list[0]; ?>"  name="next" />
	</form>

</body>

</html>
