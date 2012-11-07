<?php
$webRoot = $this->app->getWebRoot();
$jsRoot  = $webRoot . "js/";
?>
<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
  <?php
  if(isset($header->title)) echo html::title($header->title);
  css::import($this->app->getClientTheme() . 'style.css');

  js::exportConfigVars();
  js::import($jsRoot . 'my.js', $config->version);

  if(isset($pageCss)) css::internal($pageCss);
  echo html::icon($webRoot . 'favicon.ico');
  ?>
  <link rel="stylesheet" href="resources/css/style.css" type="text/css" media="screen" />  
</head>
    <body onload="document.getElementById('firstInput').focus();">
		<div id="login-wrapper" class="png_bg">
			<div id="login-top">
				<h1>登录</h1>
			</div>
            <div id="page-title"></div>
			<div id="login-content">
				<form method="post" target="hiddenwin">
                    <div class="blank20"></div>
					<p>
						<label><?php echo $lang->user->account;?></label>
						<input class="text-input" id="firstInput" type="text" name="account"/>
					</p>
					<div class="clear"></div>
					<p>
						<label><?php echo $lang->user->password;?></label>
						<input class="text-input" type="password" name="password"/>
					</p>
					<div class="clear"></div>
					<p id="remember">
						<input type="checkbox" mame="keepLogin"/>
                        &nbsp;&nbsp;<?php echo $lang->user->keepLogin['on'];?>&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo html::submitButton($lang->user->login, "class='button'");?>
					</p>
					<div class="clear"></div>
				</form>
			</div>
		</div> <!-- End #login-wrapper -->
        <div class="blank50"></div>
<?php include "../../common/view/footer.html.php"?>
