<?php
namespace Topxia\WebBundle\Twig\Extension;

class DataDict
{
	private static $dict = array(
		'courseStatus' => array(
			'draft' => '未发布',
			'published' => '已发布',
			'closed' => '已关闭'
		),
		'courseStatus:html' => array(
			'draft' => '<span class="text-muted">未发布</span>',
			'published' => '<span class="text-success">已发布</span>',
			'closed' => '<span class="text-danger">已关闭</span>'
		),
		'fileType' => array(
			'video' => '视频',
			'audio' => '音频',
			'document' => '文档',
			'image' => '图片',
			'other' => '其他'
		),
		'fileType:html' => array(
			'video' => '<span class="glyphicon glyphicon-facetime-video text-success">视频</span>',
			'audio' => '<span class="glyphicon glyphicon-music text-success">音频</span>',
			'document' => '<span class="glyphicon glyphicon-briefcase text-success">文档</span>',
			'image' => '<span class="glyphicon glyphicon-picture text-success">图片</span>',
			'other' => '<span class="glyphicon glyphicon-question-sign text-success">其他</span>',
		),
		'orderStatus' => array(
			'created' => '未付款',
			'paid' => '已付款',
			'refunding' => '退款中',
            'refunded' => '已退款',
			'cancelled' => '已关闭',
		),
		'orderStatus:html' => array(
			'created' => '<span class="text-muted">未付款</span>',
			'paid' => '<span class="text-success">已付款</span>',
			'refunding' => '<span class="text-warning">退款中</span>',
            'refunded' => '<span class="text-danger">已退款</span>',
			'cancelled' => '<span class="text-muted">已关闭</span>',
		),
		'refundStatus' => array(
			'created'  => '已申请',
			'success' => '退款成功',
			'failed' => '退款失败',
			'cancelled' => '已取消',
		),
		'refundStatus:html' => array(
			'created'  => '<span class="text-warning">已申请</span>',
			'success' => '<span class="text-success">退款成功</span>',
			'failed' => '<span class="text-danger">退款失败</span>',
			'cancelled' => '<span class="text-muted">已取消</span>',
		),
		'payment' => array(
			'alipay' => '支付宝'
		),
		'threadType' => array(
			'discussion'=> '话题',
			'question' => '问答',
		),
		'contentType' => array(
            'article' => '文章',
            'activity' => '活动',
            'page' => '页面',
        ),
        'dateType' => array(
            'today' => '今日',
            'yesterday' => '昨日',
            'this_week' => '本周',
            'last_week' => '上周',
            'this_month' => '本月',
            'last_month' => '上月',
    	),
        'contentStatus' => array(
            'published' => '已发布',
            'unpublished' => '未发布',
            'trash' => '回收站',
    	),
        'contentStatus:html' => array(
            'published' => '<span class="text-success">已发布</span>',
            'unpublished' => '<span class="text-muted">未发布</span>',
            'trash' => '<span class="text-warning">回收站</span>',
    	),
    	'lessonType'=> array(
    		'video' => '视频',
    		'audio' => '音频',
    		'text' => '图文'
		),
		'userRole' => array(
			'ROLE_USER' => '会员',
			'ROLE_TEACHER' => '教师',
			'ROLE_ADMIN' => '管理员',
			'ROLE_SUPER_ADMIN' => '超级管理员'
		),
		'userKeyWordType' => array(
			'nickname' => '用户名',
			'email' => '邮件地址',
			'loginIp' => '登录IP'
		),
		'logLevel' => array(
			'info' => '提示',
			'warning' => '警告',
			'error' => '错误'
		),
		'logLevel:html' => array(
			'info' => '<span>提示</span>',
			'warning' => '<span class="text-warning">警告</span>',
			'error' => '<span class="text-danger">错误</span>'
		),
		'userType' => array(
			'default' => '网站注册',
			'weibo' => '微博登录',
			'renren' => '人人连接',
			'qq' => 'QQ登录',
			'douban' => '豆瓣连接'
		),
		'questionType' => array(
	    	'single_choice' => '单选题',
	    	'choice' => '多选题',
            'uncertain_choice' => '不定项选择题',
	    	'fill' => '填空题',
	    	'determine' => '判断题',
	    	'essay' => '问答题',
	    	'material' => '材料题',
        ),
        'difficulty' => array(
	    	'simple' => '简单',
	    	'normal' => '一般',
	    	'difficulty' => '困难',
        ),

	);

	public static function dict($type)
	{
		return isset(self::$dict[$type]) ? self::$dict[$type] : array();
	}

	public static function text($type, $key)
	{
		if (!isset(self::$dict[$type])) {
			return null;
		}

		if (!isset(self::$dict[$type][$key])) {
			return null;
		}

		return self::$dict[$type][$key];
	}

}