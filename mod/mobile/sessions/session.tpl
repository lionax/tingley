<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="thickbox/thickbox.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.crosshair.js"></script>
<link rel="stylesheet" href="thickbox/thickbox.css" type="text/css" media="screen" />

<div class="headline">{$lang.session}</div>

<table width="100%" border="0" cellpadding="5" cellspacing="1">
	
	<tr>
		<td width="25%">{$lang.track}:</td>
		<td width="25%"><a href="{$track.url}">{$track.title}</a></td>
	</tr>
	
	<tr>
		<td width="25%">{$lang.length}:</td>
		<td>{$track.length} Meter</td>
	</tr>
	
	<tr>
		<td>{$lang.end}:</td>
		<td><strong>{$session.time}</strong> {$lang.o_clock}</td>
	</tr>
	
	<tr>
		<td>{$lang.date}:</td>
		<td>{$session.date}</td>
	</tr>
	
	<tr>
		<td>{$lang.driver}:</td>
		<td><a href="{$user.url}">{$user.prename} '<strong>{$user.nickname}</strong>' {$user.lastname}</a></td>
	</tr>
	
	<tr>
		<td>{$lang.car}:</td>
		<td><strong>{$car.title}</strong></td>
	</tr>
	
</table>

<div class="headline">{$lang.stat}</div>

<table width="100%" border="0" cellpadding="5" cellspacing="1">
	<tr>
		<td>{$lang.laps}:</td>
		<td><strong>{$stat.laps}</strong></td>
	</tr>
	<tr>
		<td>{$lang.fastest_lap}:</td>
		<td>{$lang.lap} {$fastest_lap.real_number} - <strong>{$fastest_lap.duration_str}</strong></td>
	</tr>
	<tr>
		<td>{$lang.average_lap_duration}:</td>
		<td><strong>{$stat.average_lap_duration}</strong></td>
	</tr>
	<tr>
		<td>{$lang.duration_sum}:</td>
		<td><strong>{$stat.duration_sum_str}</strong></td>
	</tr>
</table>

<div class="headline">{$lang.plot}</div>

<div id="placeholder" style="width:100%;height:300px"></div>

<div align="center">Laps</div>

<script id="source" language="javascript" type="text/javascript">
{$plot_script}
</script>

<div class="headline">{$lang.laps}</div>

<table width="100%" border="0" cellpadding="5" cellspacing="0">
	<tr>
		<th width="100">{$lang.lap}</th>
		<th width="110">{$lang.lap_duration}</th>
		<th width="250">{$lang.average_speed}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
	{section name=i loop=$laps}
	<tr{if $laps[i].lap_number == $fastest_lap.lap_number} bgcolor="#EDEDED"{/if}>
		<td style="border-bottom:1px dashed #CCC;">{$laps[i].lap_number - 1}</td>
		<td style="border-bottom:1px dashed #CCC;">{if $laps[i].duration > 0}{$laps[i].duration_str}{else}{$lang.pitstop}{/if}</td>
		<td style="border-bottom:1px dashed #CCC;">{if $laps[i].duration > 0}{math equation="(length / (time / 10000000)) * 3.6" length = $track.length time = $laps[i].duration format="%.2f"} km/h{else}-{/if}</td>
		<td style="border-bottom:1px dashed #CCC;">
			{if $laps[i].lap_number == $fastest_lap.lap_number}
				{$lang.fastest_lap}
			{/if}
		</td>
		<td align="right" style="border-bottom:1px dashed #CCC;">
			{if $isallowed}
				<form action="" method="post">
					<input type="submit" name="removeLap" value="X" />
					<input type="hidden" name="lap" value="{$laps[i].lap_number}" />
				</form>
			{else}
				&nbsp;
			{/if}
		</td>
	</tr>
	{/section}
</table>

<div class="headline">{$lang.car}</div>

<table width="100%" border="0" cellpadding="5" cellspacing="1">
    
	<tr>
		<td width="25%">{$lang.name}:</td>
        <td>{$car.title}</td>
        <td rowspan="3" align="right" valign="top">
        	<a href="media/cars/{$car.picture}" class="thickbox">
				<img src="mod/default/tracks/thumbs.php?width=100&file=../../../media/cars/{$car.picture}" 
				border="0" />
			</a>
        </td>
    </tr>
	
   	<tr>
		<td width="25%">{$lang.producer}:</td>
        <td>{$car.producer}</td>
    </tr>
    
    <tr>
        <td>{$lang.chassis}:</td>
        <td>{$car.chassis}</td>
    </tr>

</table>

<div class="headline">{$lang.setup}</div>

<table width="100%" border="0" cellpadding="5" cellspacing="1">
	<tr>
		<td width="25%">{$lang.setup}:</td>
		<td width="25%">{$setup.title}</td>
		<td width="25%">&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	
	<tr>
		<td>{$lang.speed_control}:</td>
		<td>{$setup.speed_control}</td>
		<td>{$lang.motor}:</td>
		<td>{$setup.motor}</td>
	</tr>
	
	<tr>
		<td>{$lang.battery}:</td>
		<td>{$setup.battery}</td>
		<td>{$lang.tires}:</td>
		<td>{$setup.tires}</td>
	</tr>
	
	<tr>
		<td>{$lang.damper}:</td>
		<td>{$setup.damper}</td>
		<td>{$lang.damper_pos}:</td>
		<td>{$setup.damper_pos}</td>
	</tr>
	
	<tr>
		<td>{$lang.pinion}:</td>
		<td>{$setup.pinion}</td>
		<td>{$lang.gear}:</td>
		<td>{$setup.gear}</td>
	</tr>
	
	<tr>
		<td>{$lang.comment}:</td>
		<td colspan="3">{$setup.comment}</td>
	</tr>
	
</table>