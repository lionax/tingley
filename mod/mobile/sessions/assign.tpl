<script type="text/javascript" src="js/jquery.js"></script>
{literal}
<script type="text/javascript">
  function contentloader() {
    $("#setup").load("ajax_request.php?&carid="+$("#carid").val()+"&path=mod/default/sessions/listsetups.php&none="+$("#none").val());
  }
</script>
{/literal}
<div class="headline">{$lang.assign_car_and_setup}</div>

<form action="" method="post">
	<input type="hidden" name="none" id="none" value="{$lang.none}" />
    <table width="100%" border="0" cellpadding="5" cellspacing="1">
        <tr>
            <td width="25%">{$lang.car}:</td>
            <td>
                <select name="carid" id="carid" onChange="javascript:contentloader();">
                	<option value="0">{$lang.none}</option>
                    {section name=i loop=$cars}
                    <option value="{$cars[i].carid}"{if $cars[i].carid == $car.carid} selected="selected"{/if}>{$cars[i].title}</option>
                    {/section}
                </select>
            </td>
        </tr>
        <tr>
            <td>{$lang.setup}:</td>
            <td>
                <div id="setup">
				{if $car.carid > 0}
				<select name="setupid">
					<option value="0">{$lang.none}</option>
					{section name=i loop=$setups}
					<option value="{$setups[i].setupid}"{if $setups[i].setupid == $session.setupid} selected="selected"{/if}>{$setups[i].title}</option>
					{/section}
				</option>
				{else}
					{$lang.please_select_car}
				{/if}
                </div>
            </td>
        </tr>
    </table>
    
    <p><input type="submit" name="save" value="{$lang.save}" /></p>

</form>