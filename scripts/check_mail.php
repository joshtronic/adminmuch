<?php

// Sets the server name manually as the value isn't present from the CLI
$_SERVER['SERVER_NAME'] = 'parkmuch.com';

$path = '/home/gravityboulevard/domains/images.parkmuch.com/';

// Sets up our PICKLES environment
require '/usr/share/pickles/pickles.php';
$config = new Config($path . 'config.xml');
$error  = new Error();
$db     = new DB($config, $error);

Logger::write('check_mail', 'Check mail job has started');

// Connects to the mail bo
$mailbox = imap_open('{mail.parkmuch.com:143/novalidate-cert}INBOX', 'submission@parkmuch.com', '**********');

// Pulls the number of messages
$count = imap_num_msg($mailbox);

$thin_line  = "\n" . '----------------------------------------------------------------' . "\n";
$thick_line = "\n" . '================================================================' . "\n";

$alerted = false;

// Loops through the messages
for ($i = 1; $i <= $count; $i++)
{
	echo "\n" . $thick_line . 'Message ' . $i . ' of ' . $count . ': ' . $thin_line;

	// Pulls information from the message header
	$header  = imap_headerinfo($mailbox, $i);
	$time    = date('Y-m-d H:i:s', strtotime($header->date));
	$from    = $header->fromaddress;
	$subject = $header->subject;

	// Gets the message structure
	$structure = imap_fetchstructure($mailbox, $i);

	// Pulls the plain text and HTML message body
	$message = get_part($mailbox, $i, 'TEXT/PLAIN', $structure);

	// Pulls the attachment if any
	$attachments = array();
	if (isset($structure->parts) && count($structure->parts))
	{
		$part_count = count($structure->parts);

		for ($j = 0; $j < $part_count; $j++)
		{
			if ($structure->parts[$j]->ifdparameters)
			{
				foreach ($structure->parts[$j]->dparameters as $object)
				{
					if (strtolower($object->attribute) == 'filename')
					{
						$attachments[$j]['is_attachment'] = true;
						$attachments[$j]['filename']      = $object->value;
					}
				}
			}

			if ($structure->parts[$j]->ifparameters)
			{
				foreach ($structure->parts[$j]->parameters as $object)
				{
					if (strtolower($object->attribute) == 'name')
					{
						$attachments[$j]['is_attachment'] = true;
						$attachments[$j]['name'] = $object->value;
					}
				}
			}

			if ($attachments[$j]['is_attachment'])
			{
				$attachments[$j]['attachment'] = imap_fetchbody($mailbox, $i, $j + 1);

				// 3 = BASE64
				if ($structure->parts[$j]->encoding == 3)
				{ 
					$attachments[$j]['attachment'] = base64_decode($attachments[$j]['attachment']);
				}
				// 4 = QUOTED-PRINTABLE
				elseif ($structure->parts[$j]->encoding == 4)
				{
					$attachments[$j]['attachment'] = quoted_printable_decode($attachments[$j]['attachment']);
				}
			}
		}
	}

	echo 'Time:    ' . $time . "\n";
	echo 'From:    ' . $from . "\n";
	echo 'Subject: ' . $subject;
	echo $thin_line . 'Message: ' . $thin_line . "\n" . $message;

	if (count($attachments) > 0)
	{
		$displayed = false;
		foreach ($attachments as $attachment)
		{
			if ($displayed == false)
			{
				echo $thin_line . 'Attachments:';

				$displayed = true;
			}

			$data = array(
				'sender'      => $from,
				'subject'     => $subject,
				'message'     => $message,
				'received_at' => $time
			);

			$id            = $db->insert('incoming', $data);
			$incoming_path = $path . 'incoming/' . $id . '/';

			if (!file_exists($incoming_path))
			{
				mkdir($incoming_path, 0777, true);
			}

			// Places each attachment in the appropriate location
			file_put_contents($incoming_path . $attachment['filename'], $attachment['attachment']);

			//chmod($incoming_path, 0777);
			chgrp($incoming_path, 'www-data');
			chown($incoming_path, 'www-data');

			//chmod($incoming_path . $attachment['filename'], 0777);
			chgrp($incoming_path . $attachment['filename'], 'www-data');
			chown($incoming_path . $attachment['filename'], 'www-data');

			echo "\n" . $attachment['name'];
		}

		// Marks the message for deletion
		imap_delete($mailbox, $i);

		// Sends an alert if one hasn't been sent already
		if ($alerted == false)
		{
			mail('8134952668@tmomail.net', 'NEW PARK MUCH?! SUBMISSION', 'Get on that shit!');
			$alerted = true;
		}
	}

	echo $thick_line;
}

// Closes the mail box
imap_expunge($mailbox);
imap_close($mailbox);

Logger::write('check_mail', 'Check mail job has completed');

function get_mime_type($structure)
{
	$primary_mime_type = array('TEXT', 'MULTIPART','MESSAGE', 'APPLICATION', 'AUDIO','IMAGE', 'VIDEO', 'OTHER');
	
	if ($structure->subtype)
	{
		return $primary_mime_type[(int)$structure->type] . '/' . $structure->subtype;
	}

	return 'TEXT/PLAIN';
}

function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false)
{
	if (!$structure)
	{
		$structure = imap_fetchstructure($stream, $msg_number);
	}

	if ($structure)
	{
		if ($mime_type == get_mime_type($structure)) 
		{
			if(!$part_number) 
			{
				$part_number = "1";
			}

			$text = imap_fetchbody($stream, $msg_number, $part_number);

			if ($structure->encoding == 3)
			{
				return imap_base64($text);
			}
			elseif ($structure->encoding == 4)
			{
				return imap_qprint($text);
			}
			else
			{
				return $text;
			}
		}

		// Multi-part
		if($structure->type == 1)
		{
			while (list($index, $sub_structure) = each($structure->parts))
			{
				if ($part_number)
				{
					$prefix = $part_number . '.';
				}

				$data = get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));

				if ($data)
				{
					return $data;
				}
			}
		}
	}

	return false;
}

?>
