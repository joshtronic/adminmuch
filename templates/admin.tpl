{foreach from=$module.messages item=message name=messages}
	{if $smarty.foreach.messages.first}
		<table border="1">
			<tr>
				<th>ID</th>
				<th>From</th>
				<th>Subject</th>
				<th>Received At</th>
			</tr>
	{/if}
	<tr>
		<td><a href="/message/{$message.id}">{$message.id}</a></td>
		<td>{$message.sender|htmlentities}</td>
		<td>{$message.subject}</td>
		<td>{$message.received_at}</td>
	</tr>
	{if $smarty.foreach.messages.last}</table>{/if}
{foreachelse}
	<em>No incoming submissions at this time</em>
{/foreach}
