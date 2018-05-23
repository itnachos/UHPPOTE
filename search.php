<?php
/**
 * User: carbonsphere
 * Date: 15/08/2017
 * This is an example that can be used to search for UHPPOTE controller board thats on the same network
 */

include "UHPPOTE.php";


$a = new uhppote();

/*
 * Get Search Command from UHPPOTE Class
 */
$cmd = $a->getCmdHex('search');

echo "Send the folling command to network\n$cmd\n";

/*
 *  $ip/$port  of controller board
 *  255.255.255.255 is broadcasting to network address
 *  Normally UHPPOTE controller port is static 60000
 */
//$ip = "255.255.255.255";
$ip = "192.168.1.255";
$port = 60000;
$sock = createSocket();

// Enable broadcast
//socket_set_option($sock, 1, 6, TRUE);
socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, TRUE);
$timeout = array('sec' => 5, 'usec' => 0);
socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, $timeout);
//socket_set_block($sock);
//socket_bind($sock, "192.168.1.6", 60000);

/*
 *  Input is binary format of command
 */
$input = hex2bin($cmd);

echo "Sending....\n";
if (!socket_sendto($sock, $input, strlen($input), 0, $ip, $port)) {
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    echo "There is error\n";
    exit;
}

/*
 * Once the command is broadcast, now we need to listen for return status
 */
echo "Listening for return status\n";
$reply = getReturnPacket2($sock);

/*
 * Process returned message. Returned message is in binary format
 * So we revert it into hex before passing into procMessages
 */
echo "Processing return status\n";
$procmsg = $a->procCmd(bin2hex($reply));


var_dump($procmsg);

echo "Success!\n";


function createSocket()
{
    if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);

        die("Couldn't create socket: [$errorcode] $errormsg \n");
    }
    return $sock;
}


function getReturnPacket($sock)
{
    if (socket_recv($sock, $reply, 2045, MSG_WAITALL) === false)
    {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);

        die("Receive socket Error: [$errorcode] $errormsg \n");
    }
    return $reply;
}


function getReturnPacket2($sock)
{

    $bytes = socket_recvfrom($sock, $reply, 2048, 0, $ip, $port);
//    echo "Message : < $reply > , $ip : $port\n";
//    echo "buf: [$reply] bytes: [$bytes] \n";
    if($bytes === 0)
    {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);

        die("Receive socket Error: [$errorcode] $errormsg \n");
    }
    return $reply;
}


?>
