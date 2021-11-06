<?php
class GmOauth_Action extends Typecho_Widget implements Widget_Interface_Do
{
    public function action(){
        
    }
    public function GmOauth(){
        $this->ref();
        $site = $_GET['site'];
        if($site){
            $plugin = Typecho_Widget::widget('Widget_Options')->plugin('GmOauth');
            if($plugin->$site){
                $this->response->redirect('https://sso.gmit.vip/'.$_GET['site'].'/redirect?redirect_url='.Typecho_Common::url('GmOauth/Callback', Helper::options()->index));
            }else{
                throw new Typecho_Exception(_t('未开通此第三方登陆'));
            }
        }
    }
    
    public function GmOauthBind(){
        $code = $_GET['code'];
        if($code){
            $db = Typecho_Db::get();
            Typecho_Widget::widget('Widget_User')->to($user);
            Typecho_Widget::widget('Widget_Options')->to($options);
            $curl = curl_init();
        	curl_setopt($curl, CURLOPT_URL, 'https://sso.gmit.vip/api');
        	curl_setopt($curl, CURLOPT_HEADER, 0);
        	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        	curl_setopt($curl, CURLOPT_POST, 1);
        	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        	curl_setopt($curl, CURLOPT_POSTFIELDS, [
        	    'code' => $code,
        	    'action' => 'info'
        	]);
        	$info = curl_exec($curl);
        	curl_close($curl);
        	$info = json_decode($info,true);
        	if($info['code'] == 1){
        	    $query = $db->select()->from('table.gm_oauth')->where('openid = ?',$info['data']['openid']); 
                $IsUser = $db->fetchAll($query);
                if(count($IsUser)){
                    echo '<script>alert("该第三方账号已被其它账号绑定");window.location.href="'.$options->adminUrl.'extending.php?panel=GmOauth/console.php";</script>';
                }else{
                    $addGm = array(
                        'uid'=> $user->uid,
                        'app'=> $info['data']['app'],
                        'openid' => $info['data']['openid'],
                        'time' => time(),
                    );
                    $insert = $db->insert('table.gm_oauth')->rows($addGm);
                    $insertId = $db->query($insert);
                    if($insertId){
                        echo '<script>alert("绑定成功");window.location.href="'.$options->adminUrl.'extending.php?panel=GmOauth/console.php";</script>';
                    }else{
                        echo '<script>alert("插件内部错误，请联系开发者");window.location.href="'.$options->adminUrl.'extending.php?panel=GmOauth/console.php";</script>';
                    }
                }
        	}else{
        	    echo '<script>alert("'.$res['msg'].'");window.location.href="'.$options->adminUrl.'extending.php?panel=GmOauth/console.php";</script>';
        	}
        }else{
            echo '<script>alert("非法访问");window.location.href="/";</script>';
        }
    }
    public function GmOauthCallback(){
        $db = Typecho_Db::get();
        $code = @$_GET['code'];
        if($code){
            $curl = curl_init();
        	curl_setopt($curl, CURLOPT_URL, 'https://sso.gmit.vip/api');
        	curl_setopt($curl, CURLOPT_HEADER, 0);
        	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        	curl_setopt($curl, CURLOPT_POST, 1);
        	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        	curl_setopt($curl, CURLOPT_POSTFIELDS, [
        	    'code' => $code,
        	    'action' => 'info'
        	]);
        	$info = curl_exec($curl);
        	curl_close($curl);
        	$info = json_decode(trim($info,chr(239).chr(187).chr(191)),true);
        	if(@$info['code'] == 1){
        	    $query = $db->select()->from('table.gm_oauth')->where('openid = ?',$info['data']['openid']); 
                $IsUser = $db->fetchAll($query);
                if(count($IsUser)){
                    $this->SetLogin($IsUser[0]['uid']);
                    $this->Ok();
                }else{
                    $hasher = new PasswordHash(8, true);
                    $UserName = $this->UserName();
                    $data = array(
                        'name' => $UserName,
                        'screenName' => $info['data']['nickname'],
                        'password' => $hasher->HashPassword($UserName),
                        'created' => time(),
                        'group' => 'subscriber'
                    );
                    $add = Typecho_Widget::widget('Widget_Abstract_Users')->insert($data);
                    $addGm = array(
                        'uid'=> $add,
                        'app'=> $info['data']['app'],
                        'openid' => $info['data']['openid'],
                        'time' => time(),
                    );
                    if($add){
                        $insert = $db->insert('table.gm_oauth')->rows($addGm);
                        $insertId = $db->query($insert);
                        if($insertId){
                            $this->SetLogin($add);
                            $this->Ok();
                        }else{
                            throw new Typecho_Exception(_t('内部错误'));
                            exit();
                        }
                    }else{
                        throw new Typecho_Exception(_t('内部错误'));
                        exit();
                    }
                }
        	}else{
        	    throw new Typecho_Exception(_t($info['msg']));
                exit();
        	}
        }else {
            throw new Typecho_Exception(_t('回调代码错误！'));
            exit();
        }
    }
    
    private function UserName(){
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $UserName = "";
        for ( $i = 0; $i < 6; $i++ ){
            $UserName .= @$chars[mt_rand(0, strlen($chars))];
        }
        return strtoupper(base_convert(time() - 1420070400, 10, 36)).$UserName;
    }
    
    //设置登录
    protected function SetLogin($uid, $expire = 30243600) {
        $db = Typecho_Db::get();
        Typecho_Widget::widget('Widget_User')->simpleLogin($uid);
        $authCode = function_exists('openssl_random_pseudo_bytes') ?
                bin2hex(openssl_random_pseudo_bytes(16)) : sha1(Typecho_Common::randString(20));
        Typecho_Cookie::set('__typecho_uid', $uid, time() + $expire);
        Typecho_Cookie::set('__typecho_authCode', Typecho_Common::hash($authCode), time() + $expire);
        //更新最后登录时间以及验证码
        $db->query($db->update('table.users')->expression('logged', 'activated')->rows(array('authCode' => $authCode))->where('uid = ?', $uid));
    }
    
    //验证授权来源
    protected function ref(){
        session_start();
        if(empty($_SERVER['HTTP_REFERER'])){
            //throw new Typecho_Exception(_t('来源验证失败！非法请求'));
            $_SESSION['ref'] = "//".$_SERVER['HTTP_HOST']; 
        }else{
            $_SESSION['ref'] = $_SERVER['HTTP_REFERER']; 
        }
    }
    
    //返回回调地址
    protected function cbref(){
        session_start();
        if(empty($_SESSION['ref'])){
            return '//'.$_SERVER['HTTP_HOST'].'/';
        }else{
            return $_SESSION['ref'];
        }
    }
    
    protected function Ok(){
        //$this->response->redirect($this->cbref());
        echo '
<!DOCTYPE html>
<html>
  <head>
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>加载中，请稍候…</title>
    <link rel="shortcut icon" href="/favicon.ico">
    <style type="text/css">@charset "UTF-8";html,body{margin:0;padding:0;width:100%;height:100%;background-color:#DB4D6D;display:flex;justify-content:center;align-items:center;font-family:"微軟正黑體";}.monster{width:110px;height:110px;background-color:#E55A54;border-radius:20px;position:relative;display:flex;justify-content:center;align-items:center;flex-direction:column;cursor:pointer;margin:10px;box-shadow:0px 10px 20px rgba(0,0,0,0.2);position:relative;animation:jumping 0.8s infinite alternate;}.monster .eye{width:40%;height:40%;border-radius:50%;background-color:#fff;display:flex;justify-content:center;align-items:center;}.monster .eyeball{width:50%;height:50%;border-radius:50%;background-color:#0C4475;}.monster .mouth{width:32%;height:12px;border-radius:12px;background-color:white;margin-top:15%;}.monster:before,.monster:after{content:"";display:block;width:20%;height:10px;position:absolute;left:50%;top:-10px;background-color:#fff;border-radius:10px;}.monster:before{transform:translateX(-70%) rotate(45deg);}.monster:after{transform:translateX(-30%) rotate(-45deg);}.monster,.monster *{transition:0.5s;}@keyframes jumping{50%{top:0;box-shadow:0px 10px 20px rgba(0,0,0,0.2);}100%{top:-50px;box-shadow:0px 120px 50px rgba(0,0,0,0.2);}}@keyframes eyemove{0%,10%{transform:translate(50%);}90%,100%{transform:translate(-50%);}}.monster .eyeball{animation:eyemove 1.6s infinite alternate;}h2{color:white;font-size:20px;margin:20px 0;}.pageLoading{position:fixed;width:100%;height:100%;left:0;top:0;display:flex;justify-content:center;align-items:center;background-color:#0C4475;flex-direction:column;transition:opacity 0.5s 0.5s;}.loading{width:200px;height:8px;margin-top:0px;border-radius:5px;background-color:#fff;overflow:hidden;transition:0.5s;}.loading .bar{background-color:#E55A54;width:0%;height:100%;}</style>
  </head>
  <body>
    <div class="pageLoading">
      <div class="monster">
        <div class="eye">
          <div class="eyeball"></div>
        </div>
        <div class="mouth"></div>
      </div><h2>页面加载中...</h2>
    </div>
    <script>  
        setTimeout(function(){
            top.location = "'.$this->cbref().'";
        }, 1000);
        setTimeout(function(){
            if(window.opener.location.href){
                window.opener.location.reload(true);self.close();
            }else{
                window.location.replace="'.$this->cbref().'";
            }
        }, 500);
        setTimeout(function(){window.opener=null;window.close();}, 50000);
    </script> 
  </body>
</html>';
    }
}
