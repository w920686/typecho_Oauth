<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
include 'config.php';
/**
 * <strong style="color:#000000;">故梦第三方登陆用户版</strong>
 * 
 * @package GmOauth
 * @author Gm
 * @version 2.1.2
 * @update: 2021-3-17
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
            $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}gm_oauth` (
              `id` int(255) NOT NULL,
              `app` text NOT NULL,
              `uid` int(255) NOT NULL,
              `openid` text NOT NULL,
              `time` text NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
            ALTER TABLE `{$prefix}gm_oauth`
              ADD PRIMARY KEY (`id`);
            ALTER TABLE `{$prefix}gm_oauth`
              MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;";
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
        $site = conf::site();
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
        $site = conf::site();
        $plugin = Typecho_Widget::widget('Widget_Options')->plugin('GmOauth');
        $html = '';
        for ($i = 0; $i < count($site); $i++) {
            $c = $site[$i]['site'];
            if($plugin->$c){
                $html .= '<a href="'.Typecho_Common::url('GmOauth', Helper::options()->index).'?site='.$c.'" class="btn btn-rounded btn-sm btn-icon btn-default" data-toggle="tooltip" data-placement="bottom" data-original-title="'.$site[$i]['name'].'账号登陆">'.$site[$i]['ico'].'</a>';
            }
        }
        echo '<div class="row text-center" style="margin-top:-5px;">'.$html.'</div>';
    }
}
