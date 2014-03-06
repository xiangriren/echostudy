DROP TABLE IF EXISTS `block`;
CREATE TABLE `block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL COMMENT '用户Id',
  `title` varchar(255) NOT NULL COMMENT '编辑时的题目',
  `content` text COMMENT '编辑区的内容',
  `code` varchar(255) NOT NULL DEFAULT '',
  `createdTime` int(11) unsigned NOT NULL,
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `block_history`;
CREATE TABLE `block_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `blockId` int(11) NOT NULL COMMENT 'blockId',
  `content` text COMMENT 'content',
  `userId` int(11) NOT NULL COMMENT 'userId',
  `createdTime` int(11) unsigned NOT NULL COMMENT 'createdTime',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='历史表';

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `serialized` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `expiredTime` (`expiredTime`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT '',
  `weight` int(11) NOT NULL DEFAULT '0',
  `groupId` int(10) unsigned NOT NULL,
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `category_group`;
CREATE TABLE `category_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `depth` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectType` varchar(32) NOT NULL,
  `objectId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `objectType` (`objectType`,`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `editor` enum('richeditor','none') NOT NULL DEFAULT 'richeditor' COMMENT '编辑器选择类型字段',
  `type` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `summary` text,
  `body` text,
  `picture` varchar(255) NOT NULL DEFAULT '',
  `template` varchar(255) NOT NULL DEFAULT '',
  `status` enum('published','unpublished','trash') NOT NULL,
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0',
  `tagIds` tinytext,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `featured` int(10) unsigned NOT NULL DEFAULT '0',
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '在列表中是否显示该条目。',
  `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `userId` int(10) unsigned NOT NULL,
  `field1` text,
  `field2` text,
  `field3` text,
  `field4` text,
  `field5` text,
  `field6` text,
  `field7` text,
  `field8` text,
  `field9` text,
  `field10` text,
  `publishedTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course`;
CREATE TABLE `course` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL,
  `subtitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '副标题',
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft' COMMENT '课程状态',
  `price` float(10,2) NOT NULL DEFAULT '0.00',
  `expiryDay` int(10) unsigned NOT NULL DEFAULT '0',
  `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程销售总收入',
  `lessonNum` int(10) unsigned NOT NULL DEFAULT '0',
  `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '排行数值',
  `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票人数',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0',
  `tags` text,
  `smallPicture` varchar(255) NOT NULL DEFAULT '',
  `middlePicture` varchar(255) NOT NULL DEFAULT '',
  `largePicture` varchar(255) NOT NULL DEFAULT '',
  `about` text,
  `teacherIds` text,
  `goals` text COMMENT '课程目标',
  `audiences` text COMMENT 'audiences',
  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `locationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上课地区ID',
  `address` varchar(255) NOT NULL DEFAULT '',
  `studentNum` int(10) unsigned NOT NULL DEFAULT '0',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_announcement`;
CREATE TABLE `course_announcement` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `courseId` int(10) NOT NULL,
  `content` text NOT NULL,
  `createdTime` int(10) NOT NULL,
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_chapter`;
CREATE TABLE `course_chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `number` int(10) unsigned NOT NULL,
  `seq` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_favorite`;
CREATE TABLE `course_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '收藏的id',
  `courseId` int(10) unsigned NOT NULL COMMENT '收藏课程的Id',
  `userId` int(10) unsigned NOT NULL COMMENT '收藏人的Id',
  `createdTime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户的收藏数据表';

DROP TABLE IF EXISTS `course_lesson`;
CREATE TABLE `course_lesson` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `chapterId` int(10) unsigned NOT NULL DEFAULT '0',
  `number` int(10) unsigned NOT NULL,
  `seq` int(10) unsigned NOT NULL,
  `free` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('unpublished','published') NOT NULL DEFAULT 'published',
  `title` varchar(255) NOT NULL,
  `summary` text,
  `tags` text,
  `type` varchar(64) NOT NULL DEFAULT '',
  `content` text,
  `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID(user_disk_file.id)',
  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
  `mediaName` varchar(255) NOT NULL DEFAULT '' COMMENT '媒体文件名称',
  `mediaUri` varchar(1024) NOT NULL DEFAULT '' COMMENT '媒体文件资源名',
  `length` int(11) unsigned DEFAULT NULL,
  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
  `quizNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '测验题目数量',
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `viewedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_lesson_learn`;
CREATE TABLE `course_lesson_learn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `courseId` int(10) unsigned NOT NULL,
  `lessonId` int(10) unsigned NOT NULL,
  `status` enum('learning','finished') NOT NULL,
  `startTime` int(10) unsigned NOT NULL DEFAULT '0',
  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_lessonId` (`userId`,`lessonId`),
  KEY `userId_courseId` (`userId`,`courseId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_material`;
CREATE TABLE `course_material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL,
  `description` text,
  `fileId` int(10) unsigned NOT NULL,
  `fileUri` varchar(255) NOT NULL DEFAULT '',
  `fileMime` varchar(255) NOT NULL DEFAULT '',
  `fileSize` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_member`;
CREATE TABLE `course_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员购买课程时的订单ID',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0',
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记数目',
  `noteLastUpdateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新的笔记更新时间',
  `isLearned` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `seq` int(10) unsigned NOT NULL DEFAULT '0',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `isVisible` tinyint(2) NOT NULL DEFAULT '1' COMMENT '可见与否，默认为可见',
  `role` enum('student','teacher') NOT NULL DEFAULT 'student',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courseId` (`courseId`,`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_note`;
CREATE TABLE `course_note` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL COMMENT '笔记作者ID',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程ID',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时ID',
  `content` text NOT NULL COMMENT '笔记内容',
  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记内容的字数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '笔记状态：0:私有, 1:公开',
  `createdTime` int(10) NOT NULL COMMENT '笔记创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_order`;
CREATE TABLE `course_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(32) NOT NULL,
  `status` enum('created','paid','refunding','refunded','cancelled') NOT NULL,
  `title` varchar(255) NOT NULL,
  `courseId` int(10) unsigned NOT NULL,
  `price` float unsigned NOT NULL,
  `isGift` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `giftTo` varchar(64) NOT NULL DEFAULT '',
  `refundId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次退款操作记录的ID',
  `userId` int(10) unsigned NOT NULL,
  `payment` enum('none','alipay','tenpay') NOT NULL DEFAULT 'none',
  `bank` varchar(32) NOT NULL DEFAULT '' COMMENT '银行编号',
  `paidTime` int(10) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_order_log`;
CREATE TABLE `course_order_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(10) unsigned NOT NULL,
  `type` varchar(32) NOT NULL,
  `message` text,
  `data` text,
  `userId` int(10) unsigned NOT NULL,
  `ip` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_order_refund`;
CREATE TABLE `course_order_refund` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `courseId` int(10) unsigned NOT NULL,
  `status` enum('created','success','failed','cancelled') NOT NULL DEFAULT 'created',
  `expectedAmount` float(10,2) unsigned DEFAULT '0.00' COMMENT '期望退款的金额，NULL代表未知，0代表不需要退款',
  `actualAmount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际退款金额，0代表无退款',
  `reasonType` varchar(64) NOT NULL DEFAULT '',
  `reasonNote` varchar(1024) NOT NULL DEFAULT '',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_review`;
CREATE TABLE `course_review` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '评论title',
  `content` text NOT NULL COMMENT '评论内容',
  `rating` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL COMMENT '评价创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_thread`;
CREATE TABLE `course_thread` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('discussion','question') NOT NULL DEFAULT 'discussion',
  `isStick` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `isElite` tinyint(10) unsigned NOT NULL DEFAULT '0',
  `isClosed` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `content` text,
  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击查看的次数',
  `followNum` int(10) unsigned NOT NULL DEFAULT '0',
  `latestPostUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `latestPostTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `course_thread_post`;
CREATE TABLE `course_thread_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0',
  `threadId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `isElite` tinyint(4) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `uri` varchar(255) NOT NULL,
  `mime` varchar(255) NOT NULL,
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `file_group`;
CREATE TABLE `file_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `public` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `friend`;
CREATE TABLE `friend` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fromId` int(10) unsigned NOT NULL,
  `toId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `installed_packages`;
CREATE TABLE `installed_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ename` varchar(255) NOT NULL COMMENT '包名称',
  `cname` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL COMMENT 'version',
  `installTime` int(11) NOT NULL COMMENT '安装时间',
  `fromVersion` varchar(255) NOT NULL DEFAULT '' COMMENT '来源',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cname` (`ename`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='已安装包';

DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
  `id` bigint(20) unsigned NOT NULL,
  `parentId` bigint(20) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `pinyin` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `module` varchar(32) NOT NULL,
  `action` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `data` text,
  `ip` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  `level` char(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `message`;
CREATE TABLE `message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信Id',
  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `message_conversation`;
CREATE TABLE `message_conversation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '会话Id',
  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
  `messageNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '此对话的信息条数',
  `latestMessageUserId` int(10) unsigned DEFAULT NULL COMMENT '最后一条信息，用Json显示',
  `latestMessageTime` int(10) unsigned NOT NULL,
  `latestMessageContent` text NOT NULL,
  `unreadNum` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `message_relation`;
CREATE TABLE `message_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conversationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对话id',
  `messageId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息Id',
  `isRead` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0表示未读',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `migration_versions`;
CREATE TABLE `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `navigation`;
CREATE TABLE `navigation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL COMMENT '文案',
  `url` varchar(300) NOT NULL COMMENT 'URL',
  `sequence` tinyint(4) unsigned NOT NULL COMMENT '显示顺序,数字替代',
  `createdTime` int(11) NOT NULL,
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL COMMENT '类型',
  `isOpen` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认1，为开启',
  `isNewWin` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认为1,另开窗口',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='导航数据表';

DROP TABLE IF EXISTS `notification`;
CREATE TABLE `notification` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT 'default',
  `content` text,
  `createdTime` int(10) unsigned NOT NULL,
  `isRead` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `question`;
CREATE TABLE `question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL DEFAULT '',
  `stem` text COMMENT '题干',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
  `answer` text COMMENT '参考答案',
  `analysis` text COMMENT '解析',
  `metas` text COMMENT '题目元信息',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类别',
  `difficulty` varchar(64) NOT NULL DEFAULT 'normal',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '从属于',
  `parentId` int(10) unsigned DEFAULT '0' COMMENT '材料父ID',
  `subCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子题数量',
  `finishedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成次数',
  `passedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功次数',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='问题表';

DROP TABLE IF EXISTS `question_category`;
CREATE TABLE `question_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '类别名称',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '从属于',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `seq` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库类别表';

DROP TABLE IF EXISTS `question_favorite`;
CREATE TABLE `question_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `questionId` int(10) unsigned NOT NULL DEFAULT '0',
  `target` varchar(255) NOT NULL DEFAULT '',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `session_id` varchar(255) NOT NULL,
  `session_value` text NOT NULL,
  `session_time` int(11) NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` longblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `testpaper`;
CREATE TABLE `testpaper` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
  `description` text COMMENT '试卷说明',
  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '限时(单位：\r\n秒)',
  `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷生成/显示模式',
  `target` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状\r\n态：draft,open,closed',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
  `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `metas` text COMMENT '题型排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `testpaper_item`;
CREATE TABLE `testpaper_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目',
  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属试卷',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
  `questionType` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类别',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分值',
  `missScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `testpaper_item_result`;
CREATE TABLE `testpaper_item_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷题目id',
  `testId` int(10) unsigned NOT NULL DEFAULT '0',
  `testPaperResultId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `questionId` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none',
  `score` float(10,1) NOT NULL DEFAULT '0.0',
  `answer` text,
  `teacherSay` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `testpaper_result`;
CREATE TABLE `testpaper_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `paperName` varchar(255) NOT NULL DEFAULT '',
  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'testId',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UserId',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
  `objectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
  `subjectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
  `teacherSay` text,
  `rightItemCount` int(10) unsigned NOT NULL DEFAULT '0',
  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷限制时间(秒)',
  `beginTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('doing','paused','reviewing','finished') NOT NULL COMMENT '状态',
  `target` varchar(255) NOT NULL DEFAULT '',
  `checkTeacherId` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedTime` int(11) NOT NULL DEFAULT '0',
  `usedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `upgrade_logs`;
CREATE TABLE `upgrade_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remoteId` int(11) NOT NULL COMMENT 'packageId',
  `installedId` int(11) DEFAULT NULL COMMENT '本地已安装id',
  `ename` varchar(32) NOT NULL COMMENT '名称',
  `cname` varchar(32) NOT NULL COMMENT '中文名称',
  `fromv` varchar(32) DEFAULT NULL COMMENT '初始版本',
  `tov` varchar(32) NOT NULL COMMENT '目标版本',
  `type` smallint(6) NOT NULL COMMENT '升级类型',
  `dbBackPath` text COMMENT '数据库备份文件',
  `srcBackPath` text COMMENT '源文件备份地址',
  `status` varchar(32) NOT NULL COMMENT '状态(ROLLBACK,ERROR,SUCCESS,RECOVERED)',
  `logtime` int(11) NOT NULL COMMENT '升级时间',
  `uid` int(10) unsigned NOT NULL COMMENT 'uid',
  `ip` varchar(32) DEFAULT NULL COMMENT 'ip',
  `reason` text COMMENT '失败原因',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='本地升级日志表';

DROP TABLE IF EXISTS `upload_files`;
CREATE TABLE `upload_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hashId` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
  `targetId` int(11) NOT NULL COMMENT '所存目标id',
  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '目标类型',
  `filename` varchar(1024) NOT NULL DEFAULT '',
  `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
  `size` bigint(20) NOT NULL DEFAULT '0',
  `etag` varchar(256) NOT NULL DEFAULT '',
  `convertHash` varchar(256) NOT NULL DEFAULT '' COMMENT '文件转换时的查询转换进度用的Hash值',
  `convertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none',
  `metas` text,
  `metas2` text,
  `type` enum('document','video','audio','image','other') NOT NULL DEFAULT 'other',
  `storage` enum('local','cloud') NOT NULL,
  `isPublic` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否公开文件',
  `canDownload` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否可下载',
  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新用户名',
  `updatedTime` int(10) unsigned DEFAULT '0',
  `createdUserId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hashId` (`hashId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `password` varchar(64) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `uri` varchar(64) NOT NULL DEFAULT '',
  `nickname` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT '',
  `tags` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(32) NOT NULL COMMENT 'default默认为网站注册, weibo新浪微薄登录',
  `point` int(11) NOT NULL DEFAULT '0',
  `coin` int(11) NOT NULL DEFAULT '0',
  `smallAvatar` varchar(255) NOT NULL DEFAULT '',
  `mediumAvatar` varchar(255) NOT NULL DEFAULT '',
  `largeAvatar` varchar(255) NOT NULL DEFAULT '',
  `emailVerified` tinyint(1) NOT NULL DEFAULT '0',
  `setup` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否初始化设置的，未初始化的可以设置邮箱、昵称。',
  `roles` varchar(255) NOT NULL,
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐',
  `promotedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `loginTime` int(11) NOT NULL DEFAULT '0',
  `loginIp` varchar(64) NOT NULL DEFAULT '',
  `loginSessionId` varchar(255) NOT NULL DEFAULT '',
  `approvalTime` int(10) unsigned NOT NULL DEFAULT '0',
  `approvalStatus` enum('unapprove','approving','approved','approve_fail') NOT NULL DEFAULT 'unapprove',
  `newMessageNum` int(10) unsigned NOT NULL DEFAULT '0',
  `newNotificationNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdIp` varchar(64) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user_approval`;
CREATE TABLE `user_approval` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL COMMENT '用户ID',
  `idcard` varchar(24) NOT NULL DEFAULT '' COMMENT '身份证号',
  `faceImg` varchar(500) NOT NULL DEFAULT '',
  `backImg` varchar(500) NOT NULL DEFAULT '',
  `truename` varchar(255) DEFAULT NULL COMMENT '名称',
  `note` text COMMENT '认证信息',
  `status` enum('unapprove','approving','approved','approve_fail') NOT NULL COMMENT '是否通过：1是 0否',
  `operatorId` int(10) unsigned DEFAULT NULL COMMENT '审核人',
  `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '申请时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户认证表';

DROP TABLE IF EXISTS `user_bind`;
CREATE TABLE `user_bind` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL,
  `fromId` varchar(32) NOT NULL,
  `toId` int(10) unsigned NOT NULL COMMENT '绑定的用户ID',
  `token` varchar(255) NOT NULL DEFAULT '',
  `refreshToken` varchar(255) NOT NULL DEFAULT '',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'token过期时间',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`fromId`),
  UNIQUE KEY `type_2` (`type`,`toId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user_fortune_log`;
CREATE TABLE `user_fortune_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `number` int(10) NOT NULL,
  `action` varchar(20) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `createdTime` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE `user_profile` (
  `id` int(10) unsigned NOT NULL,
  `truename` varchar(255) NOT NULL DEFAULT '',
  `idcard` varchar(24) NOT NULL DEFAULT '0' COMMENT '身份证号码',
  `gender` enum('male','female','secret') NOT NULL DEFAULT 'secret',
  `birthday` date DEFAULT NULL,
  `city` varchar(64) NOT NULL DEFAULT '',
  `mobile` varchar(32) NOT NULL DEFAULT '',
  `qq` varchar(32) NOT NULL DEFAULT '',
  `signature` text,
  `about` text,
  `company` varchar(255) NOT NULL DEFAULT '',
  `job` varchar(255) NOT NULL DEFAULT '',
  `weibo` varchar(255) NOT NULL DEFAULT '',
  `weixin` varchar(255) NOT NULL DEFAULT '',
  `site` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user_token`;
CREATE TABLE `user_token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`(6))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;