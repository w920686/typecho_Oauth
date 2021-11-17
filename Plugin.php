<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
define('__PLUGIN_ROOT__', __DIR__);
/**
 * <strong style="color:#000000;">故梦第三方登陆用户版</strong>
 * 
 * @package GmOauth
 * @author Gm
 * @version 2.2.1
 * @update: 2021-11-8
 * @link //www.gmit.vip
 */
class GmOauth_Plugin implements Typecho_Plugin_Interface
{
    public static $panel = 'GmOauth/console.php';
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Helper::addPanel(1, self::$panel, _t('快捷登录绑定'), _t('快捷登录绑定设置'), 'subscriber');
        Helper::addRoute('GmOauth', '/GmOauth/', 'GmOauth_Action', 'GmOauth');
        Helper::addRoute('GmOauthCallback', '/GmOauth/Callback', 'GmOauth_Action', 'GmOauthCallback');
        Helper::addRoute('GmOauthBind', '/GmOauth/Bind', 'GmOauth_Action', 'GmOauthBind');
        try {
            $db = Typecho_Db::get();
            $prefix = $db->getPrefix();
            $sql = "CREATE TABLE `{$prefix}gm_oauth` (
  `id` int(100) NOT NULL,
  `app` text NOT NULL,
  `uid` int(255) NOT NULL,
  `openid` text NOT NULL,
  `time` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `{$prefix}gm_oauth` CHANGE `id` `id` INT(100) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);";
            $db->query($sql);
            return '插件安装成功!数据库安装成功';
        } catch (Typecho_Db_Exception $e) {
            if ('42S01' == $e->getCode()) {
                return '插件安装成功!数据库已存在!';
            }
        }
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
        Helper::removePanel(1, self::$panel);
        Helper::removeRoute('GmOauth');
        Helper::removeRoute('GmOauthCallback');
        Helper::removeRoute('GmOauthBind');
        return '插件卸载成功';
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $iconfile = __PLUGIN_ROOT__."/icon.json";
        $icon = @fopen($iconfile, "r") or die("登陆按钮图标文件丢失!");
        $site = json_decode(fread($icon,filesize($iconfile)),true);
        fclose($icon);
        for ($i = 0; $i < count($site); $i++) {
            $radio = new Typecho_Widget_Helper_Form_Element_Radio($site[$i]['site'], array('1' => _t('开启'), '0' => _t('关闭')), '1', _t($site[$i]['name']));
            $form->addInput($radio);
        }
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
     
    /**
     *为header添加css文件
     * @return void
     */
    /*public static function header()
    {
        
    }*/
    
        /**
     *为footer添加js文件
     * @return void
     */
    /*public static function footer(){
        
    }*/
    
    public static function GmOauth()
    {
        $iconfile = __PLUGIN_ROOT__."/icon.json";
        $icon = @fopen($iconfile, "r") or die("登陆按钮图标文件丢失!");
        $site = json_decode(fread($icon,filesize($iconfile)),true);
        fclose($icon);
        $plugin = Typecho_Widget::widget('Widget_Options')->plugin('GmOauth');
        $html = '';
        for ($i = 0; $i < count($site); $i++) {
            $c = $site[$i]['site'];
            if($plugin->$c){
                $html .= '<a no-pjax onclick="OpenUrl(\''.Typecho_Common::url('GmOauth', Helper::options()->index).'?site='.$c.'\',\''.$site[$i]['width'].'\',\''.$site[$i]['height'].'\')" class="btn btn-rounded btn-sm btn-icon btn-default" data-toggle="tooltip" data-placement="bottom" data-original-title="'.$site[$i]['name'].'账号登陆">'.$site[$i]['ico'].'</a>';
            }
        }
        print <<<HTML
<style>
.icon{
   margin-top: 2px; 
}
</style>
<script>
let OpenUrl = function(url,iWidth,iHeight){
    let iTop = (window.screen.availHeight - 30 - iHeight) / 2;
    let iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
    let open = window.open(url, '_blank', 'height=' + iHeight + ',innerHeight=' + iHeight + ',width=' + iWidth + ',innerWidth=' + iWidth + ',top=' + iTop + ',left=' + iLeft + ',status=no,toolbar=no,menubar=no,location=no,resizable=no,scrollbars=0,titlebar=no');
    if(!open){
        window.location.href = url;
    }
}
</script>
HTML;
        print '<div class="row text-center" style="margin-top:-5px;">'.$html.'</div>';
    }
}
