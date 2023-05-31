<?php
	ob_start();
	session_start();

	// GRANT ALL PRIVILEGES ON `snowfam_latest`.`mahjong_pageview` TO app_mahjong@localhost;
	$con = mysqli_connect('localhost', 'app_mahjong', 'app_mahjong');
	mysqli_select_db($con, 'snowfam_latest');
	$con->query('SET NAMES utf8');
	$con->query('SET CHARACTER SET utf8');
	$con->query("SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

	$cf['time_ymdhis'] = date('Y-m-d H:i:s', time());
	$cf['ctime_ymdhis'] = date('Y-m-d H:i:s', time());
	$cf['ctime'] = time();
	
	$ua = $_SERVER['HTTP_USER_AGENT'];
	$ua = str_replace(chr(92), chr(92).chr(92), $ua); // \
	$ua = str_replace(chr(39), '&#039;', $ua); // '
	$cf['ua'] = $ua;
	$cf['mid'] = 0;
	$cf['document_url'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

	$SALT = 'dd8f1b35463aab9426d39f457417209b';
	$SALT2 = '7c317a007d9ffa750a97779ecbfb7fb4';
	function enchash($kx) {
		return md5(sha1($SALT).sha1($SALT2).$kx);
	}
	
	$request_url = $cf['document_url'];

	$cf['uip'] = $_SERVER['REMOTE_ADDR'];
	if (!$_COOKIE['sess_id']) {
		setcookie('sess_id', enchash(time().$cf['uip']), time() + 60*60*24*365, '/');
		$_COOKIE['sess_id'] = enchash(time().$cf['uip']);
	}

	$_SERVER['HTTP_REFERER'] = str_replace(chr(92), chr(92).chr(92), $_SERVER['HTTP_REFERER']); // \
	$_SERVER['HTTP_REFERER'] = str_replace(chr(39), '&#039;', $_SERVER['HTTP_REFERER']); // '

	require_once($_SERVER['DOCUMENT_ROOT'].'/lib/BrowserDetection.php');

	$BrowserDetection = new foroco\BrowserDetection();
	$BrowserDetectionRet['os'] = $BrowserDetection->getOS($ua)['os_name'].' '.$BrowserDetection->getOS($ua)['os_version'];
	$BrowserDetectionRet['browser'] = $BrowserDetection->getBrowser($ua)['browser_name'].' '.$BrowserDetection->getBrowser($ua)['browser_version'];
	$BrowserDetectionRet['device'] = $BrowserDetection->getDevice($ua)['device_type'];

	$q = "INSERT INTO `mahjong_pageview` SET `sessid` = '$_COOKIE[sess_id]',
											`ip` = '$_SERVER[REMOTE_ADDR]',
											`browser` = '$BrowserDetectionRet[browser]',
											`os` = '$BrowserDetectionRet[os]',
											`ref` = '$_SERVER[HTTP_REFERER]',
											`url` = '$request_url',
											`useragent` = '$ua',
											`datetime` = '$cf[time_ymdhis]'";
	$con->query($q);
?>
<!DOCTYPE HTML>
<html lang="ko" class="light">
    <head>
        <meta charset="utf-8">
        <link rel="icon" href="/favicon.ico">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="theme-color" content="#000000">
        <meta name="description" content="Riichi Mahjong Handle">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <title>Riichi Mahjong Handle</title>
        <script type="text/javascript" src="/js/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="/js/app.js?2022080902"></script>
        <link rel="stylesheet" href="/css/main.css">
    </head>
    <body>
        <noscript>Javascript가 필요한 게임입니다.</noscript>
        <div id="root">
            <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex w-80 mx-auto items-center mb-8 mt-12">
                    <h1 class="text-xl grow font-bold dark:text-white">Riichi Mahjong Handle</h1>
                    <svg id="svg-toggle-light" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" class="h-6 w-6 cursor-pointer dark:stroke-white">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg id="svg-info" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" class="h-6 w-6 cursor-pointer dark:stroke-white" >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg id="svg-chart" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" class="h-6 w-6 cursor-pointer dark:stroke-white" >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="flex w-full mx-auto items-center mb-8 mt-12">
                    <h2 id="handle-stage-info" class="text-lg w-full text-center font-bold dark:text-white"></h2>
                </div>
                <div id="handle-guess" class="pb-5">
                    <div class="flex justify-center mb-1">
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                    </div>
                    <div class="flex justify-center mb-1">
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                    </div>
                    <div class="flex justify-center mb-1">
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                    </div>
                    <div class="flex justify-center mb-1">
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                    </div>
                    <div class="flex justify-center mb-1">
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                        <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>
                    </div>
                </div>
                <div id="handle-input">
                    <div class="flex justify-center mb-1 sm:text-3xl text-2xl">
                        <button data-tile="Man1" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man1.svg" alt="1만">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man1.svg" alt="1만">
                        </button>
                        <button data-tile="Man2" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man2.svg" alt="2만">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man2.svg" alt="2만">
                        </button>
                        <button data-tile="Man3" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man3.svg" alt="3만">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man3.svg" alt="3만">
                        </button>
                        <button data-tile="Man4" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man4.svg" alt="4만">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man4.svg" alt="4만">
                        </button>
                        <button data-tile="Man5" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man5.svg" alt="5만">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man5.svg" alt="5만">
                        </button>
                        <button data-tile="Man6" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man6.svg" alt="6만">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man6.svg" alt="6만">
                        </button>
                        <button data-tile="Man7" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man7.svg" alt="7만">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man7.svg" alt="7만">
                        </button>
                        <button data-tile="Man8" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man8.svg" alt="8만">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man8.svg" alt="8만">
                        </button>
                        <button data-tile="Man9" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man9.svg" alt="9만">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man9.svg" alt="9만">
                        </button>
                    </div>
                    <div class="flex justify-center mb-1 sm:text-3xl text-2xl">
                        <button data-tile="Pin1" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin1.svg" alt="1통">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin1.svg" alt="1통">
                        </button>
                        <button data-tile="Pin2" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin2.svg" alt="2통">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin2.svg" alt="2통">
                        </button>
                        <button data-tile="Pin3" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin3.svg" alt="3통">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin3.svg" alt="3통">
                        </button>
                        <button data-tile="Pin4" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin4.svg" alt="4통">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin4.svg" alt="4통">
                        </button>
                        <button data-tile="Pin5" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin5.svg" alt="5통">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin5.svg" alt="5통">
                        </button>
                        <button data-tile="Pin6" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin6.svg" alt="6통">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin6.svg" alt="6통">
                        </button>
                        <button data-tile="Pin7" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin7.svg" alt="7통">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin7.svg" alt="7통">
                        </button>
                        <button data-tile="Pin8" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin8.svg" alt="8통">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin8.svg" alt="8통">
                        </button>
                        <button data-tile="Pin9" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin9.svg" alt="9통">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin9.svg" alt="9통">
                        </button>
                    </div>
                    <div class="flex justify-center mb-1 sm:text-3xl text-2xl">
                        <button data-tile="Sou1" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou1.svg" alt="1삭">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou1.svg" alt="1삭">
                        </button>
                        <button data-tile="Sou2" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou2.svg" alt="2삭">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou2.svg" alt="2삭">
                        </button>
                        <button data-tile="Sou3" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou3.svg" alt="3삭">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou3.svg" alt="3삭">
                        </button>
                        <button data-tile="Sou4" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou4.svg" alt="4삭">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou4.svg" alt="4삭">
                        </button>
                        <button data-tile="Sou5" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou5.svg" alt="5삭">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou5.svg" alt="5삭">
                        </button>
                        <button data-tile="Sou6" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou6.svg" alt="6삭">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou6.svg" alt="6삭">
                        </button>
                        <button data-tile="Sou7" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou7.svg" alt="7삭">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou7.svg" alt="7삭">
                        </button>
                        <button data-tile="Sou8" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou8.svg" alt="8삭">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou8.svg" alt="8삭">
                        </button>
                        <button data-tile="Sou9" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou9.svg" alt="9삭">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou9.svg" alt="9삭">
                        </button>
                    </div>
                    <div class="flex justify-center sm:text-3xl text-2xl">
                        <div class="text-xs">
                            <button id="btn-enter" data-tile="btn-enter" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 50px; height: 58px;">입력</button>
                        </div>
                        <button data-tile="Ton" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400  dark:bg-slate-600  dark:hover:bg-slate-700  dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Ton.svg" alt="Ton (East)">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Ton.svg" alt="Ton (East)">
                        </button>
                        <button data-tile="Nan" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400  dark:bg-slate-600  dark:hover:bg-slate-700  dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Nan.svg" alt="남">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Nan.svg" alt="남">
                        </button>
                        <button data-tile="Shaa" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400  dark:bg-slate-600  dark:hover:bg-slate-700  dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Shaa.svg" alt="서">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Shaa.svg" alt="서">
                        </button>
                        <button data-tile="Pei" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400  dark:bg-slate-600  dark:hover:bg-slate-700  dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pei.svg" alt="북">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pei.svg" alt="북">
                        </button>
                        <button data-tile="Haku" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400  dark:bg-slate-600  dark:hover:bg-slate-700  dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Haku.svg" alt="백">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Haku.svg" alt="백">
                        </button>
                        <button data-tile="Hatsu" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400  dark:bg-slate-600  dark:hover:bg-slate-700  dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Hatsu.svg" alt="발">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Hatsu.svg" alt="발">
                        </button>
                        <button data-tile="Chun" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400  dark:bg-slate-600  dark:hover:bg-slate-700  dark:active:bg-slate-800" style="width: 40px; height: 58px;">
                            <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Chun.svg" alt="중">
                            <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Chun.svg" alt="중">
                        </button>
                        <div class="text-xs">
                            <button data-tile="btn-delete" class="flex items-center justify-center rounded mx-0.5 cursor-pointer select-none dark:text-white light:bg-slate-200 light:hover:bg-slate-300 light:active:bg-slate-400 dark:bg-slate-600 dark:hover:bg-slate-700 dark:active:bg-slate-800" style="width: 50px; height: 58px;">지우기</button>
                        </div>
                    </div>
                </div>
                <button id="btn-info" type="button" class="mx-auto mt-8 flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 select-none">게임 정보</button>
            </div>
        </div>
        <div id="headlessui-portal-root" class="displaynone">
            <div>
                <div class="fixed z-10 inset-0 overflow-y-auto displaynone" id="headlessui-dialog-statistics" role="dialog" aria-modal="true" aria-labelledby="headlessui-dialog-title-14">
                    <div class="flex items-center justify-center min-h-screen py-10 px-4 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="headlessui-dialog-overlay-12" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&ZeroWidthSpace;</span>
                        <div id="headlessui-statistics" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle md:max-w-3xl sm:max-w-lg sm:w-full sm:p-6 dark:bg-gray-800">
                            <div class="absolute right-4 top-4">
                                <svg id="svg-close-statistics" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" class="h-6 w-6 cursor-pointer dark:stroke-white">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-center">
                                    <div id="statistics-answer-wrapper" class="mt-2 mb-4 displaynone">
                                        <h4 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-2">정답</h4>
                                        <div id="statistics-answer" class="flex justify-center mb-1"></div>
                                    </div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="headlessui-dialog-title-14">통계</h3>
                                    <div class="mt-2">
                                        <div class="flex justify-center my-2">
                                            <div class="items-center justify-center m-1 w-1/4 dark:text-white">
                                                <div class="text-3xl font-bold statistics-total-games">0</div>
                                                <div class="text-xs">총 게임 횟수</div>
                                            </div>
                                            <div class="items-center justify-center m-1 w-1/4 dark:text-white">
                                                <div class="text-3xl font-bold statistics-win-rate">100%</div>
                                                <div class="text-xs">성공률</div>
                                            </div>
                                            <div class="items-center justify-center m-1 w-1/4 dark:text-white">
                                                <div class="text-3xl font-bold statistics-current-streak">3</div>
                                                <div class="text-xs">현재 연속 정답 횟수</div>
                                            </div>
                                            <div class="items-center justify-center m-1 w-1/4 dark:text-white">
                                                <div class="text-3xl font-bold statistics-best-streak">3</div>
                                                <div class="text-xs">최고 연속 정답 횟수</div>
                                            </div>
                                        </div>
                                        <h4 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">횟수 분포</h4>
                                        <div class="columns-1 justify-left m-2 text-sm dark:text-white">
                                            <div class="flex justify-left m-1">
                                                <div class="items-center justify-center w-2">1</div>
                                                <div class="rounded-full w-full ml-2">
                                                    <div class="statistics-try-1 bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 rounded-l-full" style="width: 95%;">1</div>
                                                </div>
                                            </div>
                                            <div class="flex justify-left m-1">
                                                <div class="items-center justify-center w-2">2</div>
                                                <div class="rounded-full w-full ml-2">
                                                    <div class="statistics-try-2 bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 rounded-l-full" style="width: 95%;">1</div>
                                                </div>
                                            </div>
                                            <div class="flex justify-left m-1">
                                                <div class="items-center justify-center w-2">3</div>
                                                <div class="rounded-full w-full ml-2">
                                                    <div class="statistics-try-3 bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 rounded-l-full" style="width: 95%;">1</div>
                                                </div>
                                            </div>
                                            <div class="flex justify-left m-1">
                                                <div class="items-center justify-center w-2">4</div>
                                                <div class="rounded-full w-full ml-2">
                                                    <div class="statistics-try-4 bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 rounded-l-full" style="width: 95%;">1</div>
                                                </div>
                                            </div>
                                            <div class="flex justify-left m-1">
                                                <div class="items-center justify-center w-2">5</div>
                                                <div class="rounded-full w-full ml-2">
                                                    <div class="statistics-try-5 bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 rounded-l-full" style="width: 95%;">1</div>
                                                </div>
                                            </div>
                                            <div class="flex justify-left m-1">
                                                <div class="items-center justify-center w-2">6</div>
                                                <div class="rounded-full w-full ml-2">
                                                    <div class="statistics-try-6 bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 rounded-l-full" style="width: 95%;">1</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-5 sm:mt-6 columns-2 dark:text-white">
                                            <div>
                                                <button id="statistics-next-game" type="button" class="statistics-next-game mt-2 w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" tabindex="0">다음 게임</button>
                                            </div>
                                            <button id="statistics-share-game" type="button" class="statistics-share mt-2 w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" tabindex="0">공유하기</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="fixed z-10 inset-0 overflow-y-auto displaynone" id="headlessui-dialog-info" role="dialog" aria-modal="true" aria-labelledby="headlessui-dialog-title-18">
                    <div class="flex items-center justify-center min-h-screen py-10 px-4 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="headlessui-dialog-overlay-16" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&ZeroWidthSpace;</span>
                            <div id="headlessui-info" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle md:max-w-3xl sm:max-w-lg sm:w-full sm:p-6 dark:bg-gray-800">
                                <div class="absolute right-4 top-4">
                                    <svg id="svg-close-info" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" class="h-6 w-6 cursor-pointer dark:stroke-white">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-center">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="headlessui-dialog-title-18">게임 방법</h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                                리치 마작의 화료패를 6번 이내로 맞춰보세요.</br>
                                                매 시도마다 타일의 색이 변하면서, 얼마나 근접한 답을 제시했는지를 알려줍니다.
                                            </p>
                                            <p>&nbsp;</p>
                                            <p class="text-sm text-green-700 dark:text-green-500">
                                                화료패는 한 개 이상의 역을 가지고 있으면서,</br>
                                                모든 패가 (만수, 통수, 삭수, 자패) 순서와 (숫자)순서로 정렬되어있어야 합니다.</br>
                                                다만, (쯔모/론)화료 패는 마지막에 위치하면서 정렬되지 않는다는 점을 기억하세요.
                                            </p>
                                            <div class="flex justify-center mb-1 mt-4">
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man1.svg" alt="Man 1"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man1.svg" alt="Man 1"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man2.svg" alt="Man 2"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man2.svg" alt="Man 2"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man3.svg" alt="Man 3"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man3.svg" alt="Man 3"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin4.svg" alt="Pin 4"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin4.svg" alt="Pin 4"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin5.svg" alt="Pin 5"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin5.svg" alt="Pin 5"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin6.svg" alt="Pin 6"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin6.svg" alt="Pin 6"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou5.svg" alt="Sou 5"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou5.svg" alt="Sou 5"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou7.svg" alt="Sou 7"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou7.svg" alt="Sou 7"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-blue-500 text-white border-blue-500 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou8.svg" alt="Sou 8"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou8.svg" alt="Sou 8"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou9.svg" alt="Sou 9"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou9.svg" alt="Sou 9"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Hatsu.svg" alt="Hatsu (Green)"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Hatsu.svg" alt="Hatsu (Green)"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Hatsu.svg" alt="Hatsu (Green)"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Hatsu.svg" alt="Hatsu (Green)"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Hatsu.svg" alt="Hatsu (Green)"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Hatsu.svg" alt="Hatsu (Green)"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou5.svg" alt="Sou 5"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou5.svg" alt="Sou 5"/>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                                '8삭' 패가 손패에 있으며, 올바른 위치에 존재함을 나타냅니다.
                                            </p>
                                            <div class="flex justify-center mb-1 mt-4">
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man1.svg" alt="Man 1"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man1.svg" alt="Man 1"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man2.svg" alt="Man 2"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man2.svg" alt="Man 2"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Man3.svg" alt="Man 3"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Man3.svg" alt="Man 3"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin4.svg" alt="Pin 4"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin4.svg" alt="Pin 4"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-orange-500 dark:bg-orange-700 text-white border-orange-500 dark:border-orange-700 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin5.svg" alt="Pin 5"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin5.svg" alt="Pin 5"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Pin6.svg" alt="Pin 6"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Pin6.svg" alt="Pin 6"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou5.svg" alt="Sou 5"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou5.svg" alt="Sou 5"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou7.svg" alt="Sou 7"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou7.svg" alt="Sou 7"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou8.svg" alt="Sou 8"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou8.svg" alt="Sou 8"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou9.svg" alt="Sou 9"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou9.svg" alt="Sou 9"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Hatsu.svg" alt="Hatsu (Green)"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Hatsu.svg" alt="Hatsu (Green)"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Hatsu.svg" alt="Hatsu (Green)"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Hatsu.svg" alt="Hatsu (Green)"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Hatsu.svg" alt="Hatsu (Green)"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Hatsu.svg" alt="Hatsu (Green)"/>
                                                </div>
                                                <div class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation">
                                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/Sou5.svg" alt="Sou 5"/>
                                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/Sou5.svg" alt="Sou 5"/>
                                                </div>
                          </div>
                          <p class="text-sm text-gray-500 dark:text-gray-300">
                            '5통' 패가 손패에 있기는 하지만, 올바른 위치는 아님을 나타냅니다.
                          </p>
                          <div class="flex justify-center mb-1 mt-4">
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Man1.svg"
                                alt="Man 1"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Man1.svg"
                                alt="Man 1"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Man2.svg"
                                alt="Man 2"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Man2.svg"
                                alt="Man 2"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Man3.svg"
                                alt="Man 3"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Man3.svg"
                                alt="Man 3"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Pin4.svg"
                                alt="Pin 4"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Pin4.svg"
                                alt="Pin 4"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Pin5.svg"
                                alt="Pin 5"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Pin5.svg"
                                alt="Pin 5"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Pin6.svg"
                                alt="Pin 6"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Pin6.svg"
                                alt="Pin 6"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Sou5.svg"
                                alt="Sou 5"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Sou5.svg"
                                alt="Sou 5"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Sou7.svg"
                                alt="Sou 7"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Sou7.svg"
                                alt="Sou 7"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Sou8.svg"
                                alt="Sou 8"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Sou8.svg"
                                alt="Sou 8"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Sou9.svg"
                                alt="Sou 9"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Sou9.svg"
                                alt="Sou 9"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-slate-400 dark:bg-slate-700 text-white border-slate-400 dark:border-slate-700 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Hatsu.svg"
                                alt="Hatsu (Green)"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Hatsu.svg"
                                alt="Hatsu (Green)"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-slate-400 dark:bg-slate-700 text-white border-slate-400 dark:border-slate-700 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Hatsu.svg"
                                alt="Hatsu (Green)"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Hatsu.svg"
                                alt="Hatsu (Green)"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-slate-400 dark:bg-slate-700 text-white border-slate-400 dark:border-slate-700 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Hatsu.svg"
                                alt="Hatsu (Green)"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Hatsu.svg"
                                alt="Hatsu (Green)"
                              />
                            </div>
                            <div
                              class="last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600 border-black dark:border-slate-100 cell-animation"
                            >
                              <img
                                class="p-1 light:block dark:hidden drop-shadow-tile-light"
                                src="/tiles/light/Sou5.svg"
                                alt="Sou 5"
                              /><img
                                class="p-1 light:hidden dark:block drop-shadow-tile-dark"
                                src="/tiles/dark/Sou5.svg"
                                alt="Sou 5"
                              />
                            </div>
                          </div>
                          <p class="text-sm text-gray-500 dark:text-gray-300">
                            '발' 패가 손패의 어디에도 존재하지 않음을 나타냅니다.
                          </p>
                          <p class="text-sm text-green-700 dark:text-green-500 mt-4">
                            참고: 키보드 입력을 통해 손패를 입력할 수 있습니다. 예) 1m, 2p, 3s, 4z
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>          
    <div id="statistics-share-tooltip" class="tooltip-wrap">
        <div class="tooltip-wrap">
            <p class="context">복사 성공!</p>
            <div class="tooltip-arrow"></div>
        </div>
    </div>
    </body>
</html>