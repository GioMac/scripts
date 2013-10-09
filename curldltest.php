<?php
//George Machitidze | giomac at gmail dot com | 2013 | As is basis - do whatever you want
//v1.0
//environment variables here
//domain, URL
$domain="test.co.ge";
$URL="/10mb.file";

$DEBUG=1;

//do not change anything below this line unless you really know what you are doing!!!

//run indefinitely
set_time_limit(0);

//set TZ / required since PHP 5.3
date_default_timezone_set("Asia/Tbilisi");

echo "Starting..." . "\n";
echo "Resolving A records via DNS..." . "\n";

//resorve A records and put in array
$hosts=gethostbynamel($domain);

//die if DNS resolution failed
if (is_array($hosts)) {
     echo "Domain " . $domain . " resolves to addresses: ";
     foreach ($hosts as $ip) {
          echo $ip . " ";
     }
     echo "\n";
} else {
     die ("Error resolving host via DNS\n");
}

//set IP address to first A record resolved
$addr=$hosts[0];

echo "Address for " . $domain . " set to "  . $addr . "\n";

//flush buffers
ob_flush();
flush();

//main loop
while (1){
    //initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $addr . $URL);
    curl_setopt($ch, CURLOPT_BUFFERSIZE, 4096);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOPROGRESS, false);
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'progressCallback');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //set HTTP 1.1 Host header to access vhost
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: ' . $domain));
    curl_exec($ch);
    curl_close($ch);
    ob_flush();
    flush();
}

//progress function for cURL
function progressCallback( $download_size, $downloaded_size, $upload_size, $uploaded_size )
{
    //set initial variable values to 0
    static $time_prev = "0";
    static $prev_size = "0";   
    static $sustained = "0";

    //current time
    $time_now = microtime(true);
    //time elapsed since last check
    $time_diff = $time_now - $time_prev;

    //set check period
    if ( $time_diff > 2 ) {
	if ( $DEBUG = 1 ) {
	    echo "prev_size " . $prev_size . "\n";
	    echo "downloaded_size " . $downloaded_size . "\n";
	    echo "download_size " . $download_size . "\n";
	    echo "time_now " . $time_now . "\n";
	}
	//avoid negative values when jumping to new download session, calculate speed in bps
	if ($download_size > 0 ) {
    	    if ($downloaded_size - $prev_size < 0) {
	        $dspeed = intval(($downloaded_size - $prev_size + $download_size)*8 / $time_diff);	    
	    }
	    else {
        	$dspeed = intval(($downloaded_size - $prev_size)*8 / $time_diff);
	    }
	    //format time and date according to rfc2822
	    $ftime_now = date("c",$time_now);
	    //ignore first result (zero)
	    if ( $sustained > 0 ) {
    		$bps = $ftime_now . " "  . $dspeed . "\n";
		echo $bps;
		$fp = fopen( 'progress.txt', 'a' );
		fputs( $fp, $bps );
	    }
	    else {
	        $sustained="1";
	    }
		$time_prev = $time_now;
		$prev_size = $downloaded_size;
	}
	//set last date and size for downloads
    }
}

//close file (if will ever get to this point)
echo "Closing file...";
fclose( $fp );
echo "Application exit";
?>