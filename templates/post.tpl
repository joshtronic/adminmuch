{literal}<style>th { text-align: right; vertical-align: top }</style>{/literal}
<form method="post" action="/promote" onsubmit="if (!confirm('Did you double-check everything? Are you sure??')) return false;">
	<table>
		<tr>
			<th>Title:</th>
			<td><input type="text" name="title" value="{$module.message.subject}" /></td>
		</tr>
		<tr>
			<th>Content:</th>
			<td>
				<textarea name="content" cols="100" rows="10">
{$module.message.message}<br /><br />
Submitted by {$module.message.sender}
				</textarea>
			</td>
		</tr>
		<tr>
			<th>Attachment:</th>
			<td>
				<a href="/file/{$module.message.id}" target="_blank">{$module.message.attachment}</a>
				{if $module.message.details !== false}
					<br />
					<img src="/file/{$module.message.id}" width="200" />
				{/if}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<br />
				<input type="hidden" name="id" value="{$module.message.id}" />
				<button>Promote!</button>
			</td>
		</tr>
	</table>
</form>
