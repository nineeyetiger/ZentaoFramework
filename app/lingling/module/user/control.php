<?php
/**
 * The control file of user module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2011 青岛易软天创网络科技有限公司 (QingDao Nature Easy Soft Network Technology Co,LTD www.cnezsoft.com)
 * @license     LGPL (http://www.gnu.org/licenses/lgpl.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     user
 * @version     $Id: control.php 2108 2011-09-22 00:31:23Z wwccss $
 * @link        http://www.zentao.net
 */
class user extends control
{
    private $referer;

    /**
     * Construct 
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * View a user.
     * 
     * @param  string $account 
     * @access public
     * @return void
     */
    public function view($account)
    {
        $this->locate($this->createLink('user', 'profile', "account=$account"));
    }

    /**
     * The profile of a user.
     * 
     * @param  string $account 
     * @access public
     * @return void
     */
    public function profile($account)
    {
        $header['title'] = $this->lang->user->common . $this->lang->colon . $this->lang->user->profile;
        $position[]      = $this->lang->user->profile;

        /* Set menu. */
        $this->user->setMenu($this->user->getPairs('noempty|noclosed'), $account);

        $user = $this->user->getById($account);
        $deptPath = $this->dept->getParents($user->dept);

        $this->view->header   = $header;
        $this->view->position = $position;
        $this->view->user     = $user;

        $this->view->deptPath = $deptPath;

        $this->display();
    }

    /**
     * Set the rerferer.
     * 
     * @param  string   $referer 
     * @access private
     * @return void
     */
    private function setReferer($referer = '')
    {
        if(!empty($referer))
        {
            $this->referer = helper::safe64Decode($referer);
        }
        else
        {
            $this->referer = $this->server->http_referer ? $this->server->http_referer: '';
        }
        $this->referer = htmlspecialchars($this->referer);
    }

    public function register($deptID = 0)
    {     
        if(!empty($_POST))
        {
            $this->user->create();
            if(dao::isError()) die(js::error(dao::getError()));
            $this->session->set('registerOK', true);
            die(js::locate($this->createLink('user', 'login'), 'parent'));
        }

        $this->view->loginUrl = $this->createLink('user', 'login');
        $header['title'] = $this->lang->user->register;
        $this->view->header   = $header;

        $this->display();
    }

    /**
     * Edit a user.
     * 
     * @param  string|int $userID   the int user id or account
     * @access public
     * @return void
     */
    public function edit($userID)
    {
        $this->lang->set('menugroup.user', 'company');
        $this->lang->user->menu = $this->lang->company->menu;
        if(!empty($_POST))
        {
            $this->user->update($userID);
            if(dao::isError()) die(js::error(dao::getError()));
            die(js::locate($this->createLink('company', 'browse'), 'parent'));
        }

        $header['title'] = $this->lang->company->common . $this->lang->colon . $this->lang->user->edit;
        $position[]      = $this->lang->user->edit;
        $this->view->header   = $header;
        $this->view->position = $position;
        $this->view->user     = $this->user->getById($userID);
        $this->view->depts    = $this->dept->getOptionMenu();

        $this->display();
    }

    /**
     * User login, identify him and authorize him.
     * 
     * @access public
     * @return void
     */
    public function login($referer = '', $from = '')
    {
        $this->setReferer($referer);

        $loginLink = $this->createLink('user', 'login');
        $denyLink  = $this->createLink('user', 'deny');

        /* If user is logon, back to the rerferer. */
        if($this->user->isLogon())
        {
//            if(strpos($this->referer, $loginLink) === false and 
//               strpos($this->referer, $denyLink)  === false               
//            )
//            {
//            	die("sb<br/>".$this->config->default->module."<br/>".$referer);
//                $this->locate($this->referer);
//            }
//            else
            {
            	
                $this->locate($this->createLink($this->config->default->module));
            }
        }

        /* Passed account and password by post or get. */
        if(!empty($_POST) or (isset($_GET['account']) and isset($_GET['password'])))
        {
            $account  = '';
            $password = '';
            if($this->post->account)  $account  = $this->post->account;
            if($this->get->account)   $account  = $this->get->account;
            if($this->post->password) $password = $this->post->password;
            if($this->get->password)  $password = $this->get->password;

            $user = $this->user->identify($account, $password);

            if($user)
            {
                /* Authorize him and save to session. */
                // $user->rights = $this->user->authorize($account);
                $this->session->set('user', $user);
                $this->app->user = $this->session->user;

                /* Keep login. */
                if($this->post->keepLogin) $this->user->keepLogin($user);
                
                if($this->app->getViewType() == 'json') die(json_encode(array('status' => 'success')));
                
                /* Goto ads list */
                $url=$this->createLink($this->config->default->module, 'admin');                                   
                die(js::locate($url, 'parent'));
            }
            else
            {
                if($this->app->getViewType() == 'json'){
                	die(json_encode(array('status' => 'failed')));
                } 
                
                die(js::error($this->lang->user->loginFailed));
            }
        }
        else
        {
        	/*
            $header['title'] = $this->lang->user->login;
            $this->view->header    = $header;
            $this->view->referer   = $this->referer;
            //$this->view->s         = $this->loadModel('setting')->getItem('system', 'global', 'sn');
            $this->view->keepLogin = $this->cookie->keepLogin ? $this->cookie->keepLogin : 'off';
            */
        	$this->view->registerUrl=$this->createLink('user', 'register');
            $this->display();
        }
    }

    /**
     * Deny page.
     * 
     * @param  string $module
     * @param  string $method 
     * @param  string $refererBeforeDeny    the referer of the denied page.
     * @access public
     * @return void
     */
    public function deny($module, $method, $refererBeforeDeny = '')
    {
        $this->setReferer();
        $header['title'] = $this->lang->user->deny;
        $this->view->header            = $header;
        $this->view->module            = $module;
        $this->view->method            = $method;
        $this->view->denyPage          = $this->referer;        // The denied page.
        $this->view->refererBeforeDeny = $refererBeforeDeny;    // The referer of the denied page.
        $this->app->loadLang($module);
        $this->app->loadLang('my');
        $this->display();
        exit;
    }

    /**
     * Logout.
     * 
     * @access public
     * @return void
     */
    public function logout($referer = 0)
    {
//        $this->loadModel('action')->create('user', $this->app->user->id, 'logout');
        session_destroy();
        setcookie('za', false);
        setcookie('zp', false);
        $vars = !empty($referer) ? "referer=$referer" : '';
        $this->locate($this->createLink('user', 'login', $vars));
    }
    
    public function admin(){
    	$this->display();
    }

}
