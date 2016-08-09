<?php
/*
 Irongeek's Wall Of Shame Code ver. 1.2
Irongeek -at- irongeek.com
http://www.irongeek.com
Contributors: 
	Julien Goodwin <jgoodwin#studio442.com.au>

Just a fugly script I wrote to take a logfile from Etthercap and display 
passwords to a webpage.

Ettercap supports:
TELNET, FTP,  POP,  RLOGIN,  SSH1,  ICQ,  SMB,
       MySQL,  HTTP,  NNTP, X11, NAPSTER, IRC, RIP, BGP, SOCKS 5, IMAP 4, VNC,
       LDAP, NFS, SNMP, HALF LIFE, QUAKE 3, MSN, YMSG (other protocols  coming
       soon...)
 Some help from:
	http://www.php.net/
	http://www.theukwebdesigncompany.com/articles/article.php?article=165

Consider this code GPLed, but it would be sweet of you to link back to 
Irongeek.com if you use it.
 */

//// Configuration settings
// Refresh time (in seconds), set to 0 to disable
$refresh = 30;

/*Point the line below to the log file you are creating with:
         "ettercap -Tq -D  -m ettertest.log".
 if you get an error like:
	BUG at [ec_ui.c:ui_register:339]
	ops->input == NULL
 then try just "ettercap -Tq  -m ettertest.log" without the daemon option..
 Also, you could ARP poison the gateway if you like with a command like:
	ettercap -Tq  -m /tmp/ettercap.log -M arp /gateway-IP/ //.
*/
// Logfile generated by ettercap
$logfile = '/var/www/html/ettercap.log';

// Show duplicate entries?
$showdupes = false;

/*Set the below to just show the first X characters of the password, "all" to
show all, or "none" to show all *s */
//$showxchar = 3;
$showxchar = all;
//$showxchar = 'none';

// Show service names (instead of port numbers)
$showservnames = true;

// Do a reverse DNS query of target (WARNING! use only with a good local DNS cache)
$resolvetarget = true;

?>
<HTML>
<HEAD>
<?php if ($refresh > 0) { ?>
	<META HTTP-EQUIV="Refresh" Content = "<?= $refresh ?>; URL=index.php">
<?php } ?>
	<link href='http://fonts.googleapis.com/css?family=Luckiest+Guy' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Permanent+Marker' rel='stylesheet' type='text/css'>
	<TITLE>Wall of Sheep</TITLE>
<style type="text/css">
<!-- 
	TH {
		background-color: #000000;
		color:#FFFFFF;
	}
	H1 {
		font-family: 'Permanent Marker', cursive;
		color:#CC0000;
		font-size:72px;
	}
		
	BODY {
		background-color: #FFFFFF;
	}

	.SNMP {
	}

	.HTTP {
	}

	.TELNET {
	}

	.POP {
	}

	.FTP {
	}

	.VNC {
	}

	.SMB {
	}

	.IRC {
	}

	.YMSG {
	}

-->

.wrap1 {width: 400px; word-wrap: break-word}

</style>
</HEAD>
<BODY>
<?php

function between($somestring, $ss1, $ss2){
	if ($ss2 === false) { // That's what it does equate to in theory, just enforce it
		$ss2 = '';
	}

	preg_match('/' . $ss1 . '\s*(.*)\s*' . $ss2 . '/', $somestring, $matches);
	return $matches[1];
}

function showfirst($somestring, $chrnum) {
	global $showxchar;

	if ($showxchar == 'all') {
		return $somestring;
	} else if ($showxchar == 'none') {
		return str_pad(substr($somestring, 0, $showxchar), strlen($somestring), "*");
	} else {
		return str_pad(substr($somestring, 0, $showxchar), 10, '*');
	}
}

function padpw($string) {
	return showfirst($string, $showxchar);
}

function PrintCapItem($proto, $target, $user, $password, $info = false) {
	global $showservnames;
	global $resolvetarget;

// Generate full target data - NOTE, we assume TCP here
$server = explode(':', $target);
$host = $server[0];
$service = getservbyport($server[1],'tcp'); // Note this is a quick (and cached) operation so we do it anyway
if ((strlen($service) < 1) || ($showservnames === false)) {
	$service = $port;
} else {
	$service .= ' <small>(' . $server[1] . ')</small>';
}

if ($resolvetarget) {
	$host = gethostbyaddr($server[0]);
	if (strlen($host) < 1) {
		$host = $server[0];
	} else {
		$host .= ' <small>(' . $ip . ')</small>';
	}
}

?>	<TR CLASS="<?= $proto ?>">
		<TD><B><?= $proto ?></TD>
		<TD><?= $host ?></TD>
		<TD><?= $service ?></TD>
		<TD><div class="wrap1"><?= $user ?></div></TD>
		<TD><?= $password ?></TD>
	</TR>
<?php if ($info !== false) { ?>
	<TR CLASS="<?= $proto ?>">
		<TD></TD>
		<TD COLSPAN="4"><small><I>More Info:</I> <?= $info ?></small></TD>
	</TR>
<?php }
}

function linkify($text) {
	return preg_replace('/(https?:\/\/[a-zA-Z0-9\-\?\&\.\/\=\;]*)/','<a href="\1">\1</a>',$text);
}

$contents = file($logfile);
if ($contents === false) {
	echo 'Ettercap logfile could not be opened.';
	die();
}

$contents = array_reverse($contents);
// Note we want the latest entries first, by reversing first old values do show up, move the above line below the if to change this behaviour
if (!$showdupes) {
	$contents = array_unique($contents);
}

?>
<TABLE BORDER="0" ALIGN="CENTER">
<TR>
<TD>
<img src="sheep.png" />
</TD>
<TD>
<h1 align="center" style="margin-top:35px;">Wall of Sheep</h1>
</TD>
</TR>
</TABLE>
<hr>

<TABLE BORDER="1" ALIGN="CENTER">
<thead>
<TR>
	<TH>Protocol</TH>
	<TH colspan="2">Target</TH>
	<TH>User</TH>
	<TH>Password</TD>
</TR>
</thead>
<?php
foreach ($contents as $line ) {
	$line   = htmlentities($line);
	$proto  = trim(substr($line, 0, strpos($line, ':')));
	$target = between($line, ' : ', ' -&gt;');
	switch ($proto) {
		
/*		case 'SNMP':
			$user     = 'N/A';
			$password = padpw(between($line, '-&gt; COMMUNITY:', 'INFO:'));
//			$info     = between($line, 'INFO:', false);
			PrintCapItem($proto, $target,$user,$password, $info);
			break; */

		case 'HTTP':
			$user     = between($line, 'USER:', 'PASS:');
			$password = padpw(between($line, 'PASS: ', '  INFO:'));
			$info     = linkify(between($line, 'INFO:', false));
			PrintCapItem($proto, $target,$user,$password, $info );
			break;

		case 'TELNET':
			$user     = between($line, 'USER:', 'PASS:');
			$password = padpw(between($line, 'PASS:', false));
			PrintCapItem($proto, $target, $user, $password);
			break;

		case 'POP':
			$user     = between($line, 'USER:', 'PASS:');
			$password = padpw(between($line, 'PASS:', false));
			PrintCapItem($proto, $target, $user, $password);
			break;		

		case 'FTP':
			$user     = between($line, 'USER:', 'PASS:');
			$password = padpw(between($line, 'PASS:', false));
			PrintCapItem($proto, $target, $user, $password);
			break;	

		case 'VNC':
			$user     = 'Challenge: ' . between($line, '-&gt; Challenge:', ' Response:');
			$password = 'Response: ' . padpw(between($line, ' Response:', false));
			if($user == 'Challenge: '){break;}
			PrintCapItem($proto, $target, $user, $password);
			break;

		case 'SMB':
			$user     = between($line, 'USER:', 'HASH:');
			$password = padpw(between($line, 'HASH:', false));
			PrintCapItem($proto, $target, $user, $password);
			break;

		case 'IRC':
			$user     = between($line, 'USER:', 'PASS:');
			$password = padpw(between($line, 'PASS:', 'INFO:'));
			$info     = between($line, 'INFO:', false);
			PrintCapItem($proto, $target,$user,$password, $info );
			break;

		case 'YMSG':
			$user     = between($line,'USER:', 'HASH:');
			$password = padpw(between($line, 'HASH: ', '  - '));
			$info     = between($line, '  - ', false);
			PrintCapItem($proto, $target, $user, $password, $info );
			break;

		case 'DHCP':
			break;

		case 'SNMP':
			break; // Just add any other protocols to hide to this list

		default:
			if (strpos($line, ' : ') != 0 && strpos($line, 'PASS') != 0){
				$target    = between($line, ' : ', ' -&gt; USER:'); 
				$user      = between($line, 'USER: ', '  PASS:');
				$password  = padpw(between($line, 'PASS:', false));
				PrintCapItem($proto, $target, $user, $password);
			 break;	
			}else{
				$trash .= '<TR><TD>' . $proto . '</td><td colspan="3">' . $line . '</TD></TR>';
			}
	}
}
?>
</TABLE>
<HR>
</BODY>
</HTML>
