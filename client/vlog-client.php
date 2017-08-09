<?php

class vlog_client
{
    private $config = array();

    function __construct()
    {
        $loc = realpath(dirname(__FILE__));

        if (file_exists($loc."/config.php"))
            $this->loadConfig($loc."/config.php");
        elseif (file_exists($loc."/example.config.php"))
            $this->loadConfig($loc."/example.config.php");
        else
            die("No config found!");

        $mode = "w";

        // Moved inside WRITE (so READ defaults to ALL)
        //$host = gethostname();
        $username = exec("whoami");
        $person = $username;
        $entry = "";
        $host = "";
        $automatic = 0;

        $interactive = true;

        for($i=1; $i<$_SERVER['argc']; ++$i)
        {
            $a = $_SERVER['argv'][$i];
            if ($a == "--read" || $a == "-r")
                $mode="r";
            elseif ($a == "--host" || $a == "-h")
                $host=$_SERVER['argv'][++$i];
            elseif ($a == "--auto" || $a == "-a")
                $automatic=1;
            elseif ($a == "--name" || $a == "-n" || $a == "--person" || $a == "-p")
                $person = $_SERVER['argv'][++$i];
            elseif ($a == "--entry" || $a == "-e")
                $entry = $_SERVER['argv'][++$i];
            elseif ($a[0]!="-")
                $entry = $a;
            elseif ($a == "--nointeractive" || $a == "-ni")
                $interactive=false;
        }

        if ($mode == "w")
        {
            if ($host == "")
                $host = gethostname();
            if ($interactive)
            {
                $person = $this->readDef("Name",$person);
                $host = $this->readDef("Host",$host);
                $end=false;
                $entry="";
                echo "Entry (. on a line to end):\n";
                while(!$end)
                {
                    $l = readline("> ");
                    if ($l == ".")
                    {
                        $end=true;
                    }
                    else
                    {
                        $entry.=$l."\n";
                    }
                }
            }

            echo "\n";
            echo "Host          : ".$host."\n";
            echo "Username      : ".$username."\n";
            echo "Person        : ".$person."\n";
            echo "Entry         :\n";
            echo $entry;
            echo "\n";

            $sub = "n";
            if ($interactive)
                $sub = $this->readDef("Submit","Y");
            else
                $sub="y";

            if ($sub == "y" || $sub=="Y" || strtolower($sub)=="yes")
            {
                $res = $this->Submit($host, $username, $person, $entry);
                $json = json_decode($res,true);
                if(isset($json['success']) && $json['success']==1)
                    echo "Successfully Logged\n";
                else {
                    echo "Error Logging - Response: ".$res."\n\n";
                }
            }
            else {
                echo "Submission Cancelled\n";
            }
        }
        elseif($mode == "r")
        {
            $res = $this->Read($host);
            $json = json_decode($res,true);
            if (!isset($json['type']) || $json['type']!="log")
            {
                echo "Error - Response: ".$res."\n\n";
            }
            else
            {
                echo "vLog Read Query: ".$json['count']." entries\n\n";
                for($i=sizeof($json['entries']); $i--; $i>=0)
                {
                    $e=$json['entries'][$i];
                    echo "* ".$e['recorded']." ".$e['username']."@".$e['host']." (".$person.")\n";
                    echo trim($e['entry'])."\n\n";
                }
            }
        }
        else {
            echo "Unknown Mode\n";
            $this->displayUsage();
        }
    }

    function Submit($host, $username, $person, $entry)
    {
        $params = array(
            "host" => $host,
            "username" => $username,
            "person" => $person,
            "entry" => $entry,
            "m" => "w",
            "key" => $this->config['key'],
        );

        return $this->POST($this->config['api'],$params);
    }

    function Read($host = "")
    {
        $params = array(
            "m" => "r",
            "key" => $this->config['key'],
            "start" => 0,
            "limit" => 10,
        );

        if ($host != "")
            $params['host']=$host;

        return $this->POST($this->config['api'],$params);
    }

    function readDef($item,$def)
    {
        $r=readline($item." [".$def."]: ");
        if ($r == "")
            return $def;
        return $r;
    }

    private function loadConfig($file)
    {
        require_once($file);
        $this->config = array_merge($this->config, $vlogConfig);
    }

    private function displayUsage()
    {
        echo "Usage: log [--read] [--host H]\n";
        echo "With no switches will prompt to WRITE a log entry\n";
        echo "With --read or -r will READ last 10 log entries\n";
        echo "either for --host -h specific or if restricted, otherwise all\n";
    }

    private function POST($url, $params)
    {
        $p = array('http' => array(
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($params),
        ));

        $sc = stream_context_create($p);
        $fp = fopen($url,'rb',false,$sc);
        $resp = stream_get_contents($fp);
        fclose($fp);
        return $resp;
    }

}

$clent = new vlog_client();
