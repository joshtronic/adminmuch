{literal}<style>th { text-align: right; vertical-align: top }</style>{/literal}
<table>
	<tr>
		<th>Received At:</th>
		<td>{$module.message.received_at}</td>
	</tr>
	<tr>
		<th>From:</th>
		<td>{$module.message.sender|htmlentities}</td>
	</tr>
	<tr>
		<th>Subject:</th>
		<td>{$module.message.subject}</td>
	</tr>
	<tr>
		<th>Message:</th>
		<td>{$module.message.message|nl2br}</td>
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
			<button onclick="if (confirm('Are you sure?  Seriously, there\'s no undo')) document.location.href='/expunge/{$module.message.id}';">Expunge</button>
			<button onclick="document.location.href='/post/{$module.message.id}'">Promote</button>
		</td>
	</tr>
</table>
