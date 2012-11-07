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
    <body onload="document.getElementById('account').focus();">
		<div id="login-wrapper" class="png_bg">
			<div id="login-top">
				<h1>注册</h1>
			</div>
            <div id="page-title"></div>
			<div id="login-content">
				<form method="post" target="hiddenwin">
					<div class="notification information png_bg">
						<div class="word">
							<?php echo $lang->user->hasAccount.html::a($loginUrl, $lang->user->login);?>
						</div>
					</div>
                    <div class="blank20"></div>                    
					<p>
						<label><?php echo $lang->user->account;?></label>
						<?php echo html::input('account', '', "class='text-input'");?>
					</p>
					<div class="clear"></div>
					<p>
						<label><?php echo $lang->user->password;?></label>						
						<input class="text-input" type="password" name="password"/>
					</p>					
					<div class="clear"></div>
					<p id="remember">
						<input class="button" type="submit" value=<?php echo $lang->user->register;?> />
					</p>
                    <div class="clear"></div>
				</form>
			</div> <!-- End #login-content -->
		</div> <!-- End #login-wrapper -->
        <div class="blank50"></div>
<?php include "../../common/view/footer.html.php"?>
