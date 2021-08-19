<?php
 
 

require_once($cfg['path_ct'] . '/CTCNC.inc.php'); 
 
class CTRun extends CTCNC
{
  
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);

    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setPageTitle('run React pages');
        $page=@$_REQUEST["page"];
        if(! $page)
        {
            echo "page parametar is required ";
            return;
        }
        //chdir('E://Sites//cncdev2//htdocs//components//');  //change to new dir ReviewListComponent
        /*
        // use psexec to start in background, pipe stderr to stdout to capture pid
exec("psexec -d $command 2>&1", $output);
// capture pid on the 6th line
preg_match('/ID (\d+)/', $output[5], $matches);
$pid = $matches[1];
        */
        //$pid = exec("cd E://Sites//cncdev2//htdocs//components// ; npm run start:$page"."Component". ' > /dev/null 2>&1 & echo $!; ');

        $pid = shell_exec("cd E://Sites//cncdev2//htdocs//components// ; npm run start:$page"."Component");
        var_dump($pid);
        $this->parsePage();  
    }

 
}
