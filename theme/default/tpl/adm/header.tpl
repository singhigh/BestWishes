<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{$pageTitle}</title>
		<!--<meta http-equiv="Content-Language" content="fr" />-->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		
		<link rel="stylesheet" type="text/css" href="{$themeWebDir}/css/general.css" />
		<link rel="stylesheet" type="text/css" href="{$themeWebDir}/css/jquery-ui.css" />
		<link rel="shortcut icon" href="favicon.ico" />

		<script language="javascript" src="{$webDir}/js/jquery.js" type="text/javascript"></script>
		<script language="javascript" src="{$webDir}/js/jquery-ui.js" type="text/javascript"></script>
		<script language="javascript" src="{$webDir}/js/tools.js" type="text/javascript"></script>
		<script language="javascript" src="{$webDir}/js/functions.js" type="text/javascript"></script>
	</head>

	<body>
		<div id="global" class="ui-widget">
			<div id="header">
				<h1>{$siteName}</h1>
				<br /><h2>{$subTitle}</h2><br />
			</div>
			<div id="content">
				<div id="left_menu" class="ui-widget-content ui-corner-all" style="padding: 0.2em;">
					{if $sessionOk}
					<h2><a href="#">Manage lists</a></h2>
					<h2><a href="#">Manage users</a></h2>
					<h2><a href="#">Manage events</a></h2>
					<h2><a href="#">Manage options</a></h2>
					<h2><a href="#">Manage cache</a></h2>
					<a id="" href="#"><i>Show advanced</i></a>
					{/if}
					<h3><a href="{$webDir}">Back to main</a></h3>
				</div>
				{include file='floating_menu.tpl'}
				<div id="right_content" class="ui-widget-content ui-corner-all" style="padding: 0.2em 6px;">
