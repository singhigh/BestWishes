	{if !empty($message)}
		{include file='error_message.tpl'}
	{/if}
	<br />To log in:</strong>
	<form name="login" method="post">
		<table border="0" width="300" cellpadding="5">
			<tr><td align="left">&nbsp;&nbsp;{$lngLoginLabel}&nbsp;</td><td>
			<input type="text" id="username" name="adm_username" size="15" maxlength="15">
			</td></tr>
			<tr><td align="left">&nbsp;&nbsp;{$lngPasswordLabel}&nbsp;</td><td>
			<input type="password" name="adm_pass" size="15" maxlength="30" />
			</td></tr>
		</table>
		<input type="hidden" name="login_frm_submitted" value="1" />
		<input type="submit" name="submit" value="{$lngLoginAction}" />
	</form>
	<br /><a href="/" onclick="alert('TODO: password reset'); return false">{$lngPasswordForgot}</a>
	<script type="text/javascript">
	$('input[type="submit"]').button();
	</script>