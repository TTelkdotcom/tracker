<?php
session_start();

//cookie settings
if(!isset($_COOKIE['id']) && empty($_SESSION['id'])) {
    $_SESSION['id'] = rand(1000000,9999999);
    setcookie('id', $_SESSION['id'], time() + (86400 * 30), "/");
} elseif (!isset($_COOKIE['id']) && !empty($_SESSION['id'])) {
    setcookie('id', $_SESSION['id'], time() + (86400 * 30), "/");
} elseif (isset($_COOKIE['id']) && empty($_SESSION['id'])) {
    $_SESSION['id'] = $_COOKIE['id'];
    setcookie('id', $_COOKIE['id'], time() + (86400 * 30), "/");
} if ($_COOKIE['id'] !== $_SESSION['id']) {
    if ($_COOKIE['id'] > 1000000 && 9999999 > $_COOKIE['id']) {
        $_SESSION['id'] = $_COOKIE['id'];
    } elseif ($_SESSION['id'] > 1000000 && 9999999 > $_SESSION['id']) {
        $_COOKIE['id'] = $_SESSION['id'];
    } else {
    $_SESSION['id'] = rand(1000000,9999999);
    setcookie('id', $_SESSION['id'], time() + (86400 * 30), "/");
    }
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = "/";
if (substr($path, 0, strlen($base)) == $base) {
    $path = substr($path, strlen($base));
}

$path = explode("/", rtrim($path, "/"));

if ($path[0]=="r" || $_SESSION["tracking"]=='true') {
    $_SESSION["tracking"] = 'true';
    if ($path[0]=="r") {
        $_SESSION["rid"] = $path[1];
        track(implode('/',array_slice($path, 2)));

    } else {
        track(implode('/',$path));
    }
}

while (true) {
    if (count($path)==1) {
        $file = $path[0]=="" ? "index" : $path[0];
        break;
    }
    
    elseif (count($path)==0) {
        $file = "index";
        break;
    }

    else {
        $file = implode($path, "/");
        break;
    }
  
    //insert own options
}

$folder = __DIR__ . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR;
$file = $folder . $file. ".php";

if (file_exists($file)) {
    require $file;
}
else {
    http_response_code(404);
    require $folder . "404.php";
}


function track($url) {
    
    $rid = $_SESSION["rid"];
    
    if (!empty($_SESSION['id'])) {
        $id = $_SESSION['id'];
    } else {
        $id = $_COOKIE["id"];
    }
    
    if ($url == "") {
        $url = "home";
    }
    if (file_exists('./statistics/' . $rid . '.xml') == true) {
        
        $dom = new DOMDocument();
        $dom->formatOutput = TRUE;
        $dom->preserveWhiteSpace = FALSE;
        
        $dom->load('./statistics/' . $rid . '.xml');
        $dom->loadXML($dom);
        
        $history = $dom->getElementsByTagName('history')[0];
        
        $site = $dom->createElement('site', $url);
        
        $time = $dom->createAttribute('time');
        $time->value = date("d/m/Y") . "-" . date("H:i:s");
        $site->appendChild($time);
        
        $ip = $dom->createAttribute('ip');
        $ip->value = getIP();
        $site->appendChild($ip);
        
        $user = $dom->createAttribute('user');
        $user->value = $id;
        $site->appendChild($user);
        
        $history->appendChild($site) or die("Unable to log analytics");
        $dom->save('./statistics/' . $rid . '.xml');
    } else {
        
        $dom = new DomDocument('1.0', 'UTF-8');
        $dom->formatOutput = TRUE;
        $dom->preserveWhiteSpace = FALSE;
        
        $base = $dom->createElement('analytics');
        $dom->appendChild($base);
        
        $tid = $dom->createElement('id', $rid);
        $base->appendChild($tid);
        
        $history = $dom->createElement('history');
        $base->appendChild($history);
        
        $site = $dom->createElement('site', $url);
        $history->appendChild($site);
        
        $time = $dom->createAttribute('time');
        $time->value = date("d/m/Y") . "-" . date("H:i:s");
        $site->appendChild($time);
        
        $ip = $dom->createAttribute('ip');
        $ip->value = getIP();
        $site->appendChild($ip);
        
        $user = $dom->createAttribute('user');
        $user->value = $id;
        $site->appendChild($user);

        $dom->save('./statistics/' . $rid . '.xml') or die("Unable to log analytics");
    }
    return;
}


function getIP() {
    //whether ip is from shared internet
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    //whether ip is from the proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    //whether ip is from the remote address
    else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
?>
