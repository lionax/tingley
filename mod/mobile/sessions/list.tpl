<script src="js/jquery-ui/jquery-ui-1.7.2.custom.min.js" type="text/javascript"></script>
<link type="text/css" href="js/jquery-ui/css/ui-lightness/jquery-ui-1.7.2.custom.css" rel="stylesheet" />

{literal}
<script type="text/javascript">
	$(function() {
		$("#date").datepicker();
	});
</script>
{/literal}

<div class="headline">{$lang.sessions}</div>

<form action="" method="post">
	
	<table width="100%" border="0">
		<tr>
			<td>{$lang.track}:</td>
			<td>
				<select name="trackid">
					{section name=i loop=$tracks}
					<option value="{$tracks[i].trackid}"{if $filter.trackid == $tracks[i].trackid} selected{/if}>{$tracks[i].title}</option>
					{/section}
				</select>
			</td>
			<td>{$lang.date}:</td>
			<td>
				<input type="text" name="date" id="date" value="{$filter.date}" />
			</td>
		</tr>
	</table>
	
	<input type="submit" name="filter" value="{$lang.filter}" />
</form>

<table width="100%" border="0" cellpadding="5" cellspacing="1">
	
	<tr>
		<th>{$lang.track}</th>
		<th>{$lang.start}</th>
		<th>{$lang.car}</th>
		<th>{$lang.laps}</th>
	</tr>
	
	{section name=i loop=$sessions}
	<tr>
    	<td><a href="{$sessions[i].url}">{$sessions[i].title}</a></td>
        <td>{$sessions[i].time}</td>
		<td>{$sessions[i].car}</td>
		<td>{$sessions[i].laps}</td>
    </tr>
	{/section}
    
</table>