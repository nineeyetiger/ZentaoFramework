<?php
/**
 * The control file of common module of ZenTaoPHP.
 *
 * The author disclaims copyright to this source code.  In place of
 * a legal notice, here is a blessing:
 * 
 *  May you do good and not evil.
 *  May you find forgiveness for yourself and forgive others.
 *  May you share freely, never taking more than you give.
 */
class common extends control
{
    /**
     * The construct function.
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->common->startSession();
        $this->common->sendHeader();
        $this->common->setUser();
    }
    
    /**
     * Check the user has permission to access this method, if not, locate to the login page or deny page.
     * 
     * @access public
     * @return void
     */
    public function checkPriv()
    {
        $module = $this->app->getModuleName();
        $method = $this->app->getMethodName();
        if ($module == "vote") {
        	return true;
        }
        
        if($this->common->isOpenMethod($module, $method)) return true;
        if(!$this->loadModel('user')->isLogon() and $this->server->php_auth_user) $this->user->identifyByPhpAuth();
        if(!$this->loadModel('user')->isLogon() and $this->cookie->za) $this->user->identifyByCookie();

        if(! isset($this->app->user))
        {
            $referer  = helper::safe64Encode($this->app->getURI(true));
            $this->locate($this->createLink('user', 'login', "referer=$referer"));
        }
    }    

    /**
     * Print the run info 
     * 
     * @param  int    $startTime 
     * @access public
     * @return void
     */
    public function printRunInfo($startTime)
    {
        vprintf($this->lang->runInfo, $this->common->getRunInfo($startTime));
    }
}
