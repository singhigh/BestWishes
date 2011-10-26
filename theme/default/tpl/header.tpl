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
					<h2><a href="{$webDir}/index.php">{$lngHomeLogin}</a></h2>
					{if !empty($lists) }
						<h2>{$lngLists}</h2>
						<ul>
						{foreach from=$lists item=list}
							<li><a href="{$webDir}/list/{$list->slug}">{$list->name|ucfirst}</a>
							</li>
						{/foreach}
						<ul>
					{/if}
				</div>
				{include file='floating_menu.tpl'}
				<div id="right_content" class="ui-widget-content ui-corner-all" style="padding: 0.2em 6px;">