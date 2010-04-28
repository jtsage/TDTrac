#!/usr/bin/php
<?php
/**
 * TDTrac Mail Handler
 * 
 * Contains the mail attachment handler for the reciept code.
 * @package tdtracmail
 * @version 0.0.2
 *
 * TDTrac-Mail v0.0.2 - E-Mail attachment processor.  Please note, this 
 * portion of tdtrac is largely unsupported it works on my system, I 
 * make no promises that it will ever work for you.  Due to a license 
 * conflict with a required package, you must first also fetch 
 * MimeMailParser.class.php and attachment.class.php from:
 * 
 * http://code.google.com/p/php-mime-mail-parser/
 * 
 * Again, this is "bonus" code.  Best of luck, but I won't be supporting
 * this now or likely ever.
 * 
 * Typical postfix config:
 *   ** /etc/postfix/transport ::
 *        your.tdtrac.domain.com tdtrac:
 * 
 *   ** /etc/postfix/main.cf ::
 *        relay_domains = your.tdtrac.domain.com
 *        transport_maps = hash:/etc/postfix/transport
 *        tdtrac_destination_recipient_limit = 1
 * 
 *   ** /etc/postfix/master.cf (Other external delivery methods section) ::
 *        tdtrac    unix  -       n       n       -       -       pipe
 *          flags=DXRF user=tdtrac argv=/the/location/of/tdtracmail.php ${mailbox}
 */
require_once('MimeMailParser.class.php');

/* TDTRAC DATABASE CONFIGURATION DETAILS */
$MYSQL_SERVER = "mysql.jtsage.com";
$MYSQL_USER = "tdtrac";
$MYSQL_PASS = "tdtrac";
$MYSQL_DATABASE = "tdtrac";
$MYSQL_PREFIX = ""; // DON'T SET THIS HERE - LEAVE IT BLANK!

/* TDTRAC-MAIL CONFIGURATION DETAILS */
$TDT_INSTANT_EXIT = array("mailer-daemon", "postmaster", "abuse"); // Silently discard messages to these addresses. Not the best way to handle this.
$TDT_CONSTRAIN_SIZE = 900; // In pixels, the size of the largest side (x or y axis) of the saved image
$TDT_LOG_FILE = "/var/log/tdtracmail.log"; // Location of the log file

/* DO NOT EDIT BELOW THIS LINE! */

$logger      = fopen($TDT_LOG_FILE, 'a') or exit(75); // End with temp-error
$process     = time(); // Use the time as a process id.  Time to the nearest second since epoch.
$goodaccount = false;
$mailParse   = new MimeMailParser();
$mailParse->setStream(STDIN);

$db = mysql_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS);
if (!$db) { fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Cpuld Not Connect: " . mysql_error() . ".\n"); exit(75); }
   
$dbr = mysql_select_db($MYSQL_DATABASE, $db);
if (!$dbr) { fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Could Not Use {$MYSQL_DATABASE}: " . mysql_error() . ".\n"); exit(75); }

$destination_safe = $argv[1];

$destination_frommail 	= $mailParse->getHeader('delivered-to');
$message_subject 	= $mailParse->getHeader('subject');
$message_from		= $mailParse->getHeader('from');
$message_attachments	= $mailParse->getAttachments();

foreach ( $TDT_INSTANT_EXIT as $this_exiter ) {
	if ( strtolower($destination_safe) == $this_exiter ) {
		fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Mail from {$message_from} to {$destination_frommail} silently dropped\n");
		exit(0); // Silently drop these e-mails (mark as delivered)
	}
}

if ( count($message_attachments) < 1 ) { // too few attachments
	fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Mail from {$message_from} did not include an attachment.\n");
	exit(70); // Program error (perma) exit code
} 
if ( count($message_attachments) > 1 ) { // log many attachment attempt, allow for now.
	fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Mail from {$message_from} has more than one attachment.\n");
}

if ( ! preg_match("/\@rcpts.tdtrac.com/", $destination_frommail) ) {
	fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Mail from {$message_from} used an incorrect destination ({$destination_frommail}).\n");
	exit(67); // User not found (perma) exit code
}

$sql = "SELECT * FROM tdtracmail WHERE email = '{$destination_safe}'";
$result = mysql_query($sql, $db);

if ( mysql_num_rows($result) > 0 ) {
	$line = mysql_fetch_array($result);
	if ( preg_match("/{$line['code']}/", $message_subject )) {
		$goodaccount = true;
		$MYSQL_PREFIX = $line['prefix'];
	} else {
			fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Mail from {$message_from} passed account test, but failed the code test.\n");
			exit(70); // Program Error (perma) exit code
	}
}

if ( ! $goodaccount ) { 
	fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Mail from {$message_from} had an unknown incoming address ({$destination_safe}).\n");
	exit(67); // User not found (perma) exit code
} else {
	fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Mail from {$message_from} passed all sanity tests and is now being processed...\n");
}

foreach ($message_attachments as $this_attachment) { // Process attachments
	$image_input = imagecreatefromstring($this_attachment->getContent());
	
	if ( !$image_input ) { // Failed Image - break loop and continue - no error.
		fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Attachment from {$message_from} failed to load.  Bad or corrupt format.\n");
	} else {
		$input_width  = imagesx($image_input);
		$input_height = imagesy($image_input);
		
		$scale_factor = ( $input_width > $input_height ) ? $input_width / $TDT_CONSTRAIN_SIZE : $input_height / $TDT_CONSTRAIN_SIZE;
		
		$output_width  = $input_width / $scale_factor;
		$output_height = $input_height / $scale_factor;
		
		$image_output = imagecreatetruecolor($output_width, $output_height);
		
		$scale_worked = imagecopyresampled($image_output, $image_input, 0, 0, 0, 0, $output_width, $output_height, $input_width, $input_height);
		
		if ( !$scale_worked ) {
			fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Attacment from {$message_from} failed to scale.\n");
		} else {
			ob_start();
			imagejpeg($image_output, null, 90);
			$imageblob = ob_get_contents();
			ob_clean();
			
			$sql = sprintf(
				"INSERT INTO {$MYSQL_PREFIX}rcpts (type, name, data) VALUES ('%s', '%s', '%s')",
				mysql_real_escape_string($this_attachment->content_type),
				mysql_real_escape_string($this_attachment->filename),
				mysql_real_escape_string($imageblob)
			);
			$result = mysql_query($sql, $db);
			
			if ( !$result ) {
				fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Attachment from {$message_from} failed to save. (". mysql_error() . ").\n");
			} else {
				fwrite($logger, date("m.d.y H:i:s") . " :: ({$process}) :: Attachment from {$message_from} saved succesfully ({$destination_safe}).\n");
			}
		}
	}
}

?>
