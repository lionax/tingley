<div class="headline">{$lang.domain}{if $domain.name != ''}: {$domain.name}{/if}</div>
<form action="" method="post">
	<table width="100%" border="0">
		<tr>
			<td width="100">{$lang.name}:</td>
			<td><input type="text" name="name" value="{$domain.name}" style="width:100%;" /></td>
		</tr>
		<tr>
			<td>{$lang.template}:</td>
			<td>
				<select name="template" style="width:100%;">
					<option value="">-</option>
					{foreach from=$tlist item=item}
						<option value="{$item}"{if $domain.template == $item} selected="selected"{/if}>{$item}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td valign="top">{$lang.comment}:</td>
			<td><textarea name="comment" style="width:100%;" rows="6" cols="15">{$domain.comment}</textarea></td>
		</tr>
	</table>
	<p>
		<input type="submit" name="save" value="{$lang.save}" />
	</p>
</form>