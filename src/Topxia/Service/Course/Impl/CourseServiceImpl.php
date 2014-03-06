<?php
namespace Topxia\Service\Course\Impl;

use Symfony\Component\HttpFoundation\File\File;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class CourseServiceImpl extends BaseService implements CourseService
{

	/**
	 * Course API
	 */

	public function findCoursesByIds(array $ids)
	{
		$courses = CourseSerialize::unserializes(
            $this->getCourseDao()->findCoursesByIds($ids)
        );
        return ArrayToolkit::index($courses, 'id');
	}

	public function findLessonsByIds(array $ids)
	{
		$lessons = $this->getLessonDao()->findLessonsByIds($ids);
		$lessons = LessonSerialize::unserializes($lessons);
        return ArrayToolkit::index($lessons, 'id');
	}

	public function getCourse($id, $inChanging = false)
	{
		return CourseSerialize::unserialize($this->getCourseDao()->getCourse($id));
	}

	public function searchCourses($conditions, $sort = 'latest', $start, $limit)
	{
		$conditions = $this->_prepareCourseConditions($conditions);
		if ($sort == 'popular') {
			$orderBy =  array('hitNum', 'DESC');
		} else if ($sort == 'recommended') {
			$orderBy = array('recommendedTime', 'DESC');
		} else if ($sort == 'Rating') {
			$orderBy = array('Rating' , 'DESC');
		} else if ($sort == 'hitNum') {
			$orderBy = array('hitNum' , 'DESC');
		} else if ($sort == 'studentNum') {
			$orderBy = array('studentNum' , 'DESC');
		} else {
			$orderBy = array('createdTime', 'DESC');
		}
		
		return CourseSerialize::unserializes($this->getCourseDao()->searchCourses($conditions, $orderBy, $start, $limit));
	}

	public function searchCourseCount($conditions)
	{
		$conditions = $this->_prepareCourseConditions($conditions);
		return $this->getCourseDao()->searchCourseCount($conditions);
	}

	private function _prepareCourseConditions($conditions)
	{
		$conditions = array_filter($conditions);
		if (isset($conditions['date'])) {
			$dates = array(
				'yesterday'=>array(
					strtotime('yesterday'),
					strtotime('today'),
				),
				'today'=>array(
					strtotime('today'),
					strtotime('tomorrow'),
				),
				'this_week' => array(
					strtotime('Monday this week'),
					strtotime('Monday next week'),
				),
				'last_week' => array(
					strtotime('Monday last week'),
					strtotime('Monday this week'),
				),
				'next_week' => array(
					strtotime('Monday next week'),
					strtotime('Monday next week', strtotime('Monday next week')),
				),
				'this_month' => array(
					strtotime('first day of this month midnight'), 
					strtotime('first day of next month midnight'),
				),
				'last_month' => array(
					strtotime('first day of last month midnight'),
					strtotime('first day of this month midnight'),
				),
				'next_month' => array(
					strtotime('first day of next month midnight'),
					strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
				),
			);

			if (array_key_exists($conditions['date'], $dates)) {
				$conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
				$conditions['startTimeLessThan'] = $dates[$conditions['date']][1];
				unset($conditions['date']);
			}
		}

		if (isset($conditions['creator'])) {
			$user = $this->getUserService()->getUserByNickname($conditions['creator']);
			$conditions['userId'] = $user ? $user['id'] : -1;
			unset($conditions['creator']);
		}

		if (isset($conditions['categoryId'])) {
			$childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
			$conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
			unset($conditions['categoryId']);
		}

		return $conditions;
	}

	public function findUserLearnCourses($userId, $start, $limit)
	{
		$members = $this->getMemberDao()->findMembersByUserIdAndRole($userId, 'student', $start, $limit);

		$courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));
		foreach ($members as $member) {
			if (empty($courses[$member['courseId']])) {
				continue;
			}
			$courses[$member['courseId']]['memberIsLearned'] = $member['isLearned'];
			$courses[$member['courseId']]['memberLearnedNum'] = $member['learnedNum'];
		}
		return $courses;
	}

	public function findUserLearnCourseCount($userId)
	{
		return $this->getMemberDao()->findMemberCountByUserIdAndRole($userId, 'student', 0);
	}

	public function findUserLeaningCourseCount($userId)
	{
		return $this->getMemberDao()->findMemberCountByUserIdAndRoleAndIsLearned($userId, 'student', 0);
	}

	public function findUserLeaningCourses($userId, $start, $limit)
	{
		$members = $this->getMemberDao()->findMembersByUserIdAndRoleAndIsLearned($userId, 'student', '0', $start, $limit);

		$courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

		$sortedCourses = array();
		foreach ($members as $member) {
			if (empty($courses[$member['courseId']])) {
				continue;
			}
			$course = $courses[$member['courseId']];
			$course['memberIsLearned'] = 0;
			$course['memberLearnedNum'] = $member['learnedNum'];
			$sortedCourses[] = $course;
		}
		return $sortedCourses;
	}

	public function findUserLeanedCourseCount($userId)
	{
		return $this->getMemberDao()->findMemberCountByUserIdAndRoleAndIsLearned($userId, 'student', 1);
	}

	public function findUserLeanedCourses($userId, $start, $limit)
	{
		$members = $this->getMemberDao()->findMembersByUserIdAndRoleAndIsLearned($userId, 'student', '1', $start, $limit);
		$courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

		$sortedCourses = array();
		foreach ($members as $member) {
			if (empty($courses[$member['courseId']])) {
				continue;
			}
			$course = $courses[$member['courseId']];
			$course['memberIsLearned'] = 1;
			$course['memberLearnedNum'] = $member['learnedNum'];
			$sortedCourses[] = $course;
		}
		return $sortedCourses;
	}

	public function findUserTeachCourseCount($userId, $onlyPublished = true)
	{
		return $this->getMemberDao()->findMemberCountByUserIdAndRole($userId, 'teacher', $onlyPublished);
	}

	public function findUserTeachCourses($userId, $start, $limit, $onlyPublished = true)
	{
		$members = $this->getMemberDao()->findMembersByUserIdAndRole($userId, 'teacher', $start, $limit, $onlyPublished);

		$courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

		/**
		 * @todo 以下排序代码有共性，需要重构成一函数。
		 */
		$sortedCourses = array();
		foreach ($members as $member) {
			if (empty($courses[$member['courseId']])) {
				continue;
			}
			$sortedCourses[] = $courses[$member['courseId']];
		}
		return $sortedCourses;
	}

	public function findUserFavoritedCourseCount($userId)
	{
		return $this->getFavoriteDao()->getFavoriteCourseCountByUserId($userId);
	}

	public function findUserFavoritedCourses($userId, $start, $limit)
	{
		$courseFavorites = $this->getFavoriteDao()->findCourseFavoritesByUserId($userId, $start, $limit);
		$favoriteCourses = $this->getCourseDao()->findCoursesByIds(ArrayToolkit::column($courseFavorites, 'courseId'));
		return CourseSerialize::unserializes($favoriteCourses);
	}	

	public function createCourse($course)
	{
		if (!ArrayToolkit::requireds($course, array('title'))) {
			throw $this->createServiceException('缺少必要字段，创建课程失败！');
		}

		$course = ArrayToolkit::parts($course, array('title', 'about', 'categoryId', 'tags', 'price', 'startTime', 'endTime', 'locationId', 'address'));

		$course['status'] = 'draft';
        $course['about'] = !empty($course['about']) ? $this->getHtmlPurifier()->purify($course['about']) : '';
        $course['tags'] = !empty($course['tags']) ? $course['tags'] : '';
		$course['userId'] = $this->getCurrentUser()->id;
		$course['createdTime'] = time();
		$course['teacherIds'] = array($course['userId']);
		$course = $this->getCourseDao()->addCourse(CourseSerialize::serialize($course));
		
		$member = array(
			'courseId' => $course['id'],
			'userId' => $course['userId'],
			'role' => 'teacher',
			'createdTime' => time(),
		);

		$this->getMemberDao()->addMember($member);

		$course = $this->getCourse($course['id']);

		$this->getLogService()->info('course', 'create', "创建课程《{$course['title']}》(#{$course['id']})");

		return $course;
	}

	public function updateCourse($id, $fields)
	{
		$course = $this->getCourseDao()->getCourse($id);
		if (empty($course)) {
			throw $this->createServiceException('课程不存在，更新失败！');
		}

		$fields = $this->_filterCourseFields($fields);
		$this->getLogService()->info('course', 'update', "更新课程《{$course['title']}》(#{$course['id']})的信息", $fields);

		$fields = CourseSerialize::serialize($fields);

		return CourseSerialize::unserialize(
			$this->getCourseDao()->updateCourse($id, $fields)
		);
	}

	public function updateCourseCounter($id, $counter)
	{
		$fields = ArrayToolkit::parts($counter, array('rating', 'ratingNum', 'lessonNum'));
		if (empty($fields)) {
			throw $this->createServiceException('参数不正确，更新计数器失败！');
		}
		$this->getCourseDao()->updateCourse($id, $fields);
	}

	private function _filterCourseFields($fields)
	{
		$fields = ArrayToolkit::filter($fields, array(
			'title' => '',
			'subtitle' => '',
			'about' => '',
			'expiryDay' => 0,
			'categoryId' => 0,
			'goals' => array(),
			'audiences' => array(),
			'tags' => array(),
			'price' => 0.00,
			'startTime' => 0,
			'endTime'  => 0,
			'locationId' => 0,
			'address' => '',
		));

		if (!empty($fields['about'])) {
			$fields['about'] = $this->purifyHtml($fields['about']);
		}

		if (!empty($fields['tags'])) {
			array_walk($fields['tags'], function(&$item, $key) {
				$item = (int) $item;
			});
		}

		return $fields;
	}

    public function changeCoursePicture ($courseId, $filePath, array $options)
    {
        $course = $this->getCourseDao()->getCourse($courseId);
        if (empty($course)) {
            throw $this->createServiceException('课程不存在，图标更新失败！');
        }

        $pathinfo = pathinfo($filePath);
        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);

        $largeImage = $rawImage->copy();
        $largeImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        $largeImage->resize(new Box(480, 270));
        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeImage->save($largeFilePath, array('quality' => 90));
        $largeFileRecord = $this->getFileService()->uploadFile('course', new File($largeFilePath));

        $largeImage->resize(new Box(304, 171));
        $middleFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_middle.{$pathinfo['extension']}";
        $largeImage->save($middleFilePath, array('quality' => 90));
        $middleFileRecord = $this->getFileService()->uploadFile('course', new File($middleFilePath));

        $largeImage->resize(new Box(96, 54));
        $smallFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_small.{$pathinfo['extension']}";
        $largeImage->save($smallFilePath, array('quality' => 90));
        $smallFileRecord = $this->getFileService()->uploadFile('course', new File($smallFilePath));

        $fields = array(
        	'smallPicture' => $smallFileRecord['uri'],
        	'middlePicture' => $middleFileRecord['uri'],
        	'largePicture' => $largeFileRecord['uri'],
    	);

		$this->getLogService()->info('course', 'update_picture', "更新课程《{$course['title']}》(#{$course['id']})图片", $fields);
        
        return $this->getCourseDao()->updateCourse($courseId, $fields);
    }

	public function recommendCourse($id)
	{
		$course = $this->tryAdminCourse($id);

		$this->getCourseDao()->updateCourse($id, array(
			'recommended' => 1,
			'recommendedTime' => time(),
		));

		$this->getLogService()->info('course', 'recommend', "推荐课程《{$course['title']}》(#{$course['id']})");
	}

	public function cancelRecommendCourse($id)
	{
		$course = $this->tryAdminCourse($id);

		$this->getCourseDao()->updateCourse($id, array(
			'recommended' => 0,
			'recommendedTime' => 0,
		));

		$this->getLogService()->info('course', 'cancel_recommend', "取消推荐课程《{$course['title']}》(#{$course['id']})");
	}

	public function deleteCourse($id)
	{
		$course = $this->tryAdminCourse($id);

		$this->getMemberDao()->deleteMembersByCourseId($id);
		$this->getLessonDao()->deleteLessonsByCourseId($id);
		$this->getChapterDao()->deleteChaptersByCourseId($id);

		$this->getCourseDao()->deleteCourse($id);

		$this->getLogService()->info('course', 'delete', "删除课程《{$course['title']}》(#{$course['id']})");

		return true;
	}

	public function publishCourse($id)
	{
		$course = $this->tryManageCourse($id);
		$this->getCourseDao()->updateCourse($id, array('status' => 'published'));
		$this->getLogService()->info('course', 'publish', "发布课程《{$course['title']}》(#{$course['id']})");
	}

	public function closeCourse($id)
	{
		$course = $this->tryManageCourse($id);
		$this->getCourseDao()->updateCourse($id, array('status' => 'closed'));
		$this->getLogService()->info('course', 'close', "关闭课程《{$course['title']}》(#{$course['id']})");
	}

	public function favoriteCourse($courseId)
	{
		$user = $this->getCurrentUser();
		if (empty($user['id'])) {
			throw $this->createAccessDeniedException();
		}

		$course = $this->getCourse($courseId);
		if($course['status']!='published'){
			throw $this->createServiceException('不能收藏未发布课程');
		}

		if (empty($course)) {
			throw $this->createServiceException("该课程不存在,收藏失败!");
		}

		$favorite = $this->getFavoriteDao()->getFavoriteByUserIdAndCourseId($user['id'], $course['id']);
		if($favorite){
			throw $this->createServiceException("该收藏已经存在，请不要重复收藏!");
		}

		$this->getFavoriteDao()->addFavorite(array(
			'courseId'=>$course['id'],
			'userId'=>$user['id'], 
			'createdTime'=>time()
		));

		return true;
	}

	public function unFavoriteCourse($courseId)
	{
		$user = $this->getCurrentUser();
		if (empty($user['id'])) {
			throw $this->createAccessDeniedException();
		}

		$course = $this->getCourse($courseId);
		if (empty($course)) {
			throw $this->createServiceException("该课程不存在,收藏失败!");
		}

		$favorite = $this->getFavoriteDao()->getFavoriteByUserIdAndCourseId($user['id'], $course['id']);
		if(empty($favorite)){
			throw $this->createServiceException("你未收藏本课程，取消收藏失败!");
		}

		$this->getFavoriteDao()->deleteFavorite($favorite['id']);

		return true;
	}

	public function hasFavoritedCourse($courseId)
	{
		$user = $this->getCurrentUser();
		if (empty($user['id'])) {
			return false;
		}

		$course = $this->getCourse($courseId);
		if (empty($course)) {
			throw $this->createServiceException("课程{$courseId}不存在");
		}

		$favorite = $this->getFavoriteDao()->getFavoriteByUserIdAndCourseId($user['id'], $course['id']);

		return $favorite ? true : false;
	}

	private function autosetCourseFields($courseId)
	{
		$fields = array('type' => 'text', 'lessonNum' => 0);
		$lessons = $this->getCourseLessons($courseId);
		if (empty($lessons)) {
			$this->getCourseDao()->updateCourse($courseId, $fields);
			return ;
		}

        $counter = array('text' => 0, 'video' => 0);

        foreach ($lessons as $lesson) {
            $counter[$lesson['type']] ++;
            $fields['lessonNum'] ++;
        }

        $percents = array_map(function($value) use ($fields) {
        	return $value / $fields['lessonNum'] * 100;
        }, $counter);

        if ($percents['video'] > 50) {
            $fields['type'] = 'video';
        } else {
            $fields['type'] = 'text';
        }

		$this->getCourseDao()->updateCourse($courseId, $fields);

	}

	/**
	 * Lesslon API
	 */

	public function getCourseLesson($courseId, $lessonId)
	{
		$lesson = $this->getLessonDao()->getLesson($lessonId);
		if (empty($lesson) or ($lesson['courseId'] != $courseId)) {
			return null;
		}
		return LessonSerialize::unserialize($lesson);
	}

	public function getCourseLessons($courseId)
	{
		$lessons = $this->getLessonDao()->findLessonsByCourseId($courseId);
		return LessonSerialize::unserializes($lessons);
	}

	public function createLesson($lesson)
	{
		$lesson = ArrayToolkit::filter($lesson, array(
			'courseId' => 0,
			'chapterId' => 0,
			'free' => 0,
			'title' => '',
			'summary' => '',
			'tags' => array(),
			'type' => 'text',
			'content' => '',
			'media' => array(),
			'mediaId' => 0,
			'length' => 0,
		));

		if (!ArrayToolkit::requireds($lesson, array('courseId', 'title', 'type'))) {
			throw $this->createServiceException('参数缺失，创建课时失败！');
		}

		if (empty($lesson['courseId'])) {
			throw $this->createServiceException('添加课时失败，课程ID为空。');
		}

		$course = $this->getCourse($lesson['courseId'], true);
		if (empty($course)) {
			throw $this->createServiceException('添加课时失败，课程不存在。');
		}

		if (!in_array($lesson['type'], array('text', 'audio', 'video', 'testpaper'))) {
			throw $this->createServiceException('课时类型不正确，添加失败！');
		}

		$this->fillLessonMediaFields($lesson);



		//课程内容的过滤 @todo
		// if(isset($lesson['content'])){
		// 	$lesson['content'] = $this->purifyHtml($lesson['content']);
		// }
		if (isset($fields['title'])) {
			$fields['title'] = $this->purifyHtml($fields['title']);
		}

		// 课程处于发布状态时，新增课时，课时默认的状态为“未发布"
		$lesson['status'] = $course['status'] == 'published' ? 'unpublished' : 'published';
		$lesson['free'] = empty($lesson['free']) ? 0 : 1;
		$lesson['number'] = $this->getNextLessonNumber($lesson['courseId']);
		$lesson['seq'] = $this->getNextCourseItemSeq($lesson['courseId']);
		$lesson['userId'] = $this->getCurrentUser()->id;
		$lesson['createdTime'] = time();

		$lesson = $this->getLessonDao()->addLesson(
			LessonSerialize::serialize($lesson)
		);

		$this->updateCourseCounter($course['id'], array(
			'lessonNum' => $this->getLessonDao()->getLessonCountByCourseId($course['id'])
		));

		$this->getLogService()->info('course', 'add_lesson', "添加课时《{$lesson['title']}》({$lesson['id']})", $lesson);

		return $lesson;
	}

	private function fillLessonMediaFields(&$lesson)
	{

		if (in_array($lesson['type'], array('video', 'audio'))) {
			$media = empty($lesson['media']) ? null : $lesson['media'];
			if (empty($media) or empty($media['source']) or empty($media['name'])) {
				throw $this->createServiceException("media参数不正确，添加课时失败！");
			}

			if ($media['source'] == 'self') {
				$media['id'] = intval($media['id']);
				if (empty($media['id'])) {
					throw $this->createServiceException("media id参数不正确，添加/编辑课时失败！");
				}
				$file = $this->getUploadFileService()->getFile($media['id']);
				if (empty($file)) {
					throw $this->createServiceException('文件不存在，添加/编辑课时失败！');
				}

				$lesson['mediaId'] = $file['id'];
				$lesson['mediaName'] = $file['filename'];
				$lesson['mediaSource'] = 'self';
				$lesson['mediaUri'] = '';
			} else {
				if (empty($media['uri'])) {
					throw $this->createServiceException("media uri参数不正确，添加/编辑课时失败！");
				}
				$lesson['mediaId'] = 0;
				$lesson['mediaName'] = $media['name'];
				$lesson['mediaSource'] = $media['source'];
				$lesson['mediaUri'] = $media['uri'];
			}
		} elseif ($lesson['type'] == 'testpaper') {
			$lesson['mediaId'] = $lesson['mediaId'];
		} else {
			$lesson['mediaId'] = 0;
			$lesson['mediaName'] = '';
			$lesson['mediaSource'] = '';
			$lesson['mediaUri'] = '';
		}

		unset($lesson['media']);

		return $lesson;
	}

	public function updateLesson($courseId, $lessonId, $fields)
	{
		$course = $this->getCourse($courseId);
		if (empty($course)) {
			throw $this->createServiceException("课程(#{$courseId})不存在！");
		}

		$lesson = $this->getCourseLesson($courseId, $lessonId);
		if (empty($lesson)) {
			throw $this->createServiceException("课时(#{$lessonId})不存在！");
		}

		$fields = ArrayToolkit::filter($fields, array(
			'title' => '',
			'summary' => '',
			'content' => '',
			'media' => array(),
			'mediaId' => 0,
			'free' => 0,
			'length' => 0,
		));

		// if (isset($fields['content'])) {
		// 	$fields['content'] = $this->purifyHtml($fields['content']);
		// }

		if (isset($fields['title'])) {
			$fields['title'] = $this->purifyHtml($fields['title']);
		}

		$fields['type'] = $lesson['type'];
		$this->fillLessonMediaFields($fields);

		$lesson = LessonSerialize::unserialize(
			$this->getLessonDao()->updateLesson($lessonId, LessonSerialize::serialize($fields))
		);

		$this->getLogService()->info('course', 'update_lesson', "更新课时《{$lesson['title']}》({$lesson['id']})", $lesson);

		return $lesson;
	}

	public function deleteLesson($courseId, $lessonId)
	{
		$course = $this->getCourse($courseId);
		if (empty($course)) {
			throw $this->createServiceException("课程(#{$courseId})不存在！");
		}

		$lesson = $this->getCourseLesson($courseId, $lessonId, true);
		if (empty($lesson)) {
			throw $this->createServiceException("课时(#{$lessonId})不存在！");
		}

		$this->getLessonDao()->deleteLesson($lessonId);

		$number = 1;
		$lessons = $this->getCourseLessons($courseId);
        foreach ($lessons as $lesson) {
        	if ($lesson['number'] != $number) {
        		$this->getLessonDao()->updateLesson($lesson['id'], array('number' => $number));
        	}
        	$number ++;
        }

		$this->updateCourseCounter($course['id'], array(
			'lessonNum' => $this->getLessonDao()->getLessonCountByCourseId($course['id'])
		));

		$this->getLogService()->info('lesson', 'delete', "删除课程《{$course['title']}》(#{$course['id']})的课时 {$lesson['title']}");

		// $this->autosetCourseFields($courseId);
	}

	public function publishLesson($courseId, $lessonId)
	{
		$course = $this->tryManageCourse($courseId);

		$lesson = $this->getCourseLesson($courseId, $lessonId);
		if (empty($lesson)) {
			throw $this->createServiceException("课时#{$lessonId}不存在");
		}

		$this->getLessonDao()->updateLesson($lesson['id'], array('status' => 'published'));
	}

	public function unpublishLesson($courseId, $lessonId)
	{
		$course = $this->tryManageCourse($courseId);

		$lesson = $this->getCourseLesson($courseId, $lessonId);
		if (empty($lesson)) {
			throw $this->createServiceException("课时#{$lessonId}不存在");
		}

		$this->getLessonDao()->updateLesson($lesson['id'], array('status' => 'unpublished'));
	}

	public function getNextLessonNumber($courseId)
	{
		return $this->getLessonDao()->getLessonCountByCourseId($courseId) + 1;
	}

	public function startLearnLesson($courseId, $lessonId)
	{
		list($course, $member) = $this->tryTakeCourse($courseId);
		$user = $this->getCurrentUser();

		$learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($user['id'], $lessonId);
		if ($learn) {
			return false;
		}

		$this->getLessonLearnDao()->addLearn(array(
			'userId' => $user['id'],
			'courseId' => $courseId,
			'lessonId' => $lessonId,
			'status' => 'learning',
			'startTime' => time(),
			'finishedTime' => 0,
		));

		return true;
	}

	public function finishLearnLesson($courseId, $lessonId)
	{
		list($course, $member) = $this->tryLearnCourse($courseId);

		$learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($member['userId'], $lessonId);
		if ($learn) {
			$this->getLessonLearnDao()->updateLearn($learn['id'], array(
				'status' => 'finished',
				'finishedTime' => time(),
			));
		} else {
			$this->getLessonLearnDao()->addLearn(array(
				'userId' => $member['userId'],
				'courseId' => $courseId,
				'lessonId' => $lessonId,
				'status' => 'finished',
				'startTime' => time(),
				'finishedTime' => time(),
			));
		}

		$memberFields = array();
		$memberFields['learnedNum'] = $this->getLessonLearnDao()->getLearnCountByUserIdAndCourseIdAndStatus($member['userId'], $course['id'], 'finished');
		$memberFields['isLearned'] = $memberFields['learnedNum'] >= $course['lessonNum'] ? 1 : 0;
		$this->getMemberDao()->updateMember($member['id'], $memberFields);
	}

	public function cancelLearnLesson($courseId, $lessonId)
	{
		list($course, $member) = $this->tryLearnCourse($courseId);

		$learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($member['userId'], $lessonId);
		if (empty($learn)) {
			throw $this->createServiceException("课时#{$lessonId}尚未学习，取消学习失败。");
		}

		if ($learn['status'] != 'finished') {
			throw $this->createServiceException("课时#{$lessonId}尚未学完，取消学习失败。");
		}

		$this->getLessonLearnDao()->updateLearn($learn['id'], array(
			'status' => 'learning',
			'finishedTime' => 0,
		));

		$this->getMemberDao()->updateMember($member['id'], array(
			'learnedNum' => $this->getLessonLearnDao()->getLearnCountByUserIdAndCourseIdAndStatus($member['userId'], $course['id'], 'finished'),
		));

	}

	public function getUserLearnLessonStatus($userId, $courseId, $lessonId)
	{
		$learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($userId, $lessonId);
		if (empty($learn)) {
			return null;
		}

		return $learn['status'];
	}

	public function getUserLearnLessonStatuses($userId, $courseId)
	{
		$learns = $this->getLessonLearnDao()->findLearnsByUserIdAndCourseId($userId, $courseId) ? : array();

		$statuses = array();
		foreach ($learns as $learn) {
			$statuses[$learn['lessonId']] = $learn['status'];
		}

		return $statuses;
	}

	public function getUserNextLearnLesson($userId, $courseId)
	{
		$lessonIds = $this->getLessonDao()->findLessonIdsByCourseId($courseId);

		$learns = $this->getLessonLearnDao()->findLearnsByUserIdAndCourseIdAndStatus($userId, $courseId, 'finished');

		$learnedLessonIds = ArrayToolkit::column($learns, 'lessonId');

		$unlearnedLessonIds = array_diff($lessonIds, $learnedLessonIds);
		$nextLearnLessonId = array_shift($unlearnedLessonIds);
		if (empty($nextLearnLessonId)) {
			return null;
		}
		return $this->getLessonDao()->getLesson($nextLearnLessonId);
	}

	public function getChapter($courseId, $chapterId)
	{
		$chapter = $this->getChapterDao()->getChapter($chapterId);
		if (empty($chapter) or $chapter['courseId'] != $courseId) {
			return null;
		}
		return $chapter;
	}

	public function getCourseChapters($courseId)
	{
		return $this->getChapterDao()->findChaptersByCourseId($courseId);
	}

	public function createChapter($chapter)
	{
		$chapter['number'] = $this->getNextChapterNumber($chapter['courseId']);
		$chapter['seq'] = $this->getNextCourseItemSeq($chapter['courseId']);
		$chapter['createdTime'] = time();
		return $this->getChapterDao()->addChapter($chapter);
	}

	public function updateChapter($courseId, $chapterId, $fields)
	{
		$chapter = $this->getChapter($courseId, $chapterId);
		if (empty($chapter)) {
			throw $this->createServiceException("章节#{$chapterId}不存在！");
		}
		$fields = ArrayToolkit::parts($fields, array('title'));
		return $this->getChapterDao()->updateChapter($chapterId, $fields);
	}

	public function deleteChapter($courseId, $chapterId)
	{
		$course = $this->tryManageCourse($courseId);

		$deletedChapter = $this->getChapter($course['id'], $chapterId);
		if (empty($deletedChapter)) {
			throw $this->createServiceException(sprintf('章节(ID:%s)不存在，删除失败！', $chapterId));
		}

		$this->getChapterDao()->deleteChapter($deletedChapter['id']);

		$number = 1;
		$prevChapter = array('id' => 0);
		foreach ($this->getCourseChapters($course['id']) as $chapter) {
			if ($chapter['number'] < $deletedChapter['number']) {
				$prevChapter = $chapter;
			}
			if ($chapter['number'] != $number) {
				$this->getChapterDao()->updateChapter($chapter['id'], array('number' => $number));
			}
			$number ++;
		}

		$lessons = $this->getLessonDao()->findLessonsByChapterId($deletedChapter['id']);
		foreach ($lessons as $lesson) {
			$this->getLessonDao()->updateLesson($lesson['id'], array('chapterId' => $prevChapter['id']));
		}
	}

	public function getNextChapterNumber($courseId)
	{
		$counter = $this->getChapterDao()->getChapterCountByCourseId($courseId);
		return $counter + 1;
	}

	public function getCourseItems($courseId)
	{
		$lessons = LessonSerialize::unserializes(
			$this->getLessonDao()->findLessonsByCourseId($courseId)
		);

		$chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);

		$items = array();
		foreach ($lessons as $lesson) {
			$lesson['itemType'] = 'lesson';
			$items["lesson-{$lesson['id']}"] = $lesson;
		}

		foreach ($chapters as $chapter) {
			$chapter['itemType'] = 'chapter';
			$items["chapter-{$chapter['id']}"] = $chapter;
		}

		uasort($items, function($item1, $item2){
			return $item1['seq'] > $item2['seq'];
		});
		return $items;
	}

	public function sortCourseItems($courseId, array $itemIds)
	{
		$items = $this->getCourseItems($courseId);
		$existedItemIds = array_keys($items);

		if (count($itemIds) != count($existedItemIds)) {
			throw $this->createServiceException('itemdIds参数不正确');
		}

		$diffItemIds = array_diff($itemIds, array_keys($items));
		if (!empty($diffItemIds)) {
			throw $this->createServiceException('itemdIds参数不正确');
		}

		$lessonId = $chapterId = $seq = 0;
		$currentChapter = array('id' => 0);

		foreach ($itemIds as $itemId) {
			$seq ++;
			list($type, ) = explode('-', $itemId);
			switch ($type) {
				case 'lesson':
					$lessonId ++;
					$item = $items[$itemId];
					$fields = array('number' => $lessonId, 'seq' => $seq, 'chapterId' => $currentChapter['id']);
					if ($fields['number'] != $item['number'] or $fields['seq'] != $item['seq'] or $fields['chapterId'] != $item['chapterId']) {
						$this->getLessonDao()->updateLesson($item['id'], $fields);
					}
					break;
				case 'chapter':
					$chapterId ++;
					$item = $currentChapter = $items[$itemId];
					$fields = array('number' => $chapterId, 'seq' => $seq);
					if ($fields['number'] != $item['number'] or $fields['seq'] != $item['seq']) {
						$this->getChapterDao()->updateChapter($item['id'], $fields);
					}
					break;
			}
		}
	}

	private function getNextCourseItemSeq($courseId)
	{
		$chapterMaxSeq = $this->getChapterDao()->getChapterMaxSeqByCourseId($courseId);
		$lessonMaxSeq = $this->getLessonDao()->getLessonMaxSeqByCourseId($courseId);
		return ($chapterMaxSeq > $lessonMaxSeq ? $chapterMaxSeq : $lessonMaxSeq) + 1;
	}

	public function addMemberExpiryDays($courseId, $userId, $day)
	{
		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

		if ($member['deadline'] > 0){
			$deadline = $day*24*60*60+$member['deadline'];
		} else {
			$deadline = $day*24*60*60+time();
		}

		return $this->getMemberDao()->updateMember($member['id'], array(
			'deadline' => $deadline
		));
	}

	/**
	 * Member API
	 */
	public function searchMemberCount($conditions)
	{
		return $this->getMemberDao()->searchMemberCount($conditions);
	}
	
	public function searchMember($conditions, $start, $limit)
	{
		$conditions = $this->_prepareCourseConditions($conditions);
		return $this->getMemberDao()->searchMember($conditions, $start, $limit);
	}

	public function updateCourseMember($id, $fields)
	{
		return $this->getMemberDao()->updateMember($id, $fields);
	}

	public function getCourseMember($courseId, $userId)
	{
		return $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
	}

	public function findCourseStudents($courseId, $start, $limit)
	{
		return $this->getMemberDao()->findMembersByCourseIdAndRole($courseId, 'student', $start, $limit);
	}

	public function getCourseStudentCount($courseId)
	{
		return $this->getMemberDao()->findMemberCountByCourseIdAndRole($courseId, 'student');
	}

	public function findCourseTeachers($courseId)
	{
		return $this->getMemberDao()->findMembersByCourseIdAndRole($courseId, 'teacher', 0, self::MAX_TEACHER);
	}

	public function isCourseTeacher($courseId, $userId)
	{
		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
		if(!$member){
			return false;
		} else {
			return empty($member) or $member['role'] != 'teacher' ? false : true;
		}
	}

	public function isCourseStudent($courseId, $userId)
	{
		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
		if(!$member){
			return false;
		} else {
			return empty($member) or $member['role'] != 'student' ? false : true;
		}
	}

	public function setCourseTeachers($courseId, $teachers)
	{
		// 过滤数据
		$teacherMembers = array();
		foreach (array_values($teachers) as $index => $teacher) {
			if (empty($teacher['id'])) {
				throw $this->createServiceException("教师ID不能为空，设置课程(#{$courseId})教师失败");
			}
			$user = $this->getUserService()->getUser($teacher['id']);
			if (empty($user)) {
				throw $this->createServiceException("用户不存在或没有教师角色，设置课程(#{$courseId})教师失败");
			}

			$teacherMembers[] = array(
				'courseId' => $courseId,
				'userId' => $user['id'],
				'role' => 'teacher',
				'seq' => $index,
				'isVisible' => empty($teacher['isVisible']) ? 0 : 1,
				'createdTime' => time(),
			);
		}

		// 先清除所有的已存在的教师会员
		$existTeacherMembers = $this->findCourseTeachers($courseId);
		foreach ($existTeacherMembers as $member) {
			$this->getMemberDao()->deleteMember($member['id']);
		}

		// 逐个插入新的教师的会员数据
		$visibleTeacherIds = array();
		foreach ($teacherMembers as $member) {
			// 存在会员信息，说明该用户先前是学生会员，则删除该会员信息。
			$existMember = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $member['userId']);
			if ($existMember) {
				$this->getMemberDao()->deleteMember($existMember['id']);
			}
			$this->getMemberDao()->addMember($member);
			if ($member['isVisible']) {
				$visibleTeacherIds[] = $member['userId'];
			}
		}

		$this->getLogService()->info('course', 'update_teacher', "更新课程#{$courseId}的教师", $teacherMembers);

		// 更新课程的teacherIds，该字段为课程可见教师的ID列表
		$fields = array('teacherIds' => $visibleTeacherIds);
		$this->getCourseDao()->updateCourse($courseId, CourseSerialize::serialize($fields));
	}

	/**
	 * @todo 当用户拥有大量的课程老师角色时，这个方法效率是有就有问题咯！鉴于短期内用户不会拥有大量的课程老师角色，先这么做着。
	 */
	public function cancelTeacherInAllCourses($userId)
	{
		$count = $this->getMemberDao()->findMemberCountByUserIdAndRole($userId, 'teacher', false);
		$members = $this->getMemberDao()->findMembersByUserIdAndRole($userId, 'teacher', 0, $count, false);
		foreach ($members as $member) {
			$course = $this->getCourse($member['courseId']);

			$this->getMemberDao()->deleteMember($member['id']);

			$fields = array(
				'teacherIds' => array_diff($course['teacherIds'], array($member['userId']))
			);
			$this->getCourseDao()->updateCourse($member['courseId'], CourseSerialize::serialize($fields));
		}

		$this->getLogService()->info('course', 'cancel_teachers_all', "取消用户#{$userId}所有的课程老师角色");
	}

	public function remarkStudent($courseId, $userId, $remark)
	{
		$member = $this->getCourseMember($courseId, $userId);
		if (empty($member)) {
			throw $this->createServiceException('课程会员不存在，备注失败!');
		}
		$fields = array('remark' => empty($remark) ? '' : (string) $remark);
		return $this->getMemberDao()->updateMember($member['id'], $fields);
	}

	public function becomeStudent($courseId, $userId, $info = array())
	{
		$course = $this->getCourse($courseId);

		if (empty($course)) {
			throw $this->createNotFoundException();
		}

		if($course['status'] != 'published') {
			throw $this->createServiceException('不能加入未发布课程');
		}

		$user = $this->getUserService()->getUser($userId);
		if (empty($user)) {
			throw $this->createServiceException("用户(#{$userId})不存在，加入课程失败！");
		}

		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
		if ($member) {
			throw $this->createServiceException("用户(#{$userId})已加入课加入课程！");
		}

		if ($course['expiryDay'] > 0) {
			$deadline = $course['expiryDay']*24*60*60 + time();
		} else {
			$deadline = 0;
		}

		$order = $this->getOrderDao()->getOrder($info['orderId']);

		$fields = array(
			'courseId' => $courseId,
			'userId' => $userId,
			'orderId' => empty($info['orderId']) ? 0 : $info['orderId'],
			'deadline' => $deadline,
			'role' => 'student',
			'remark' => $order['note'],
			'createdTime' => time()
		);

		$member = $this->getMemberDao()->addMember($fields);

		$setting = $this->getSettingService()->get('course', array());
		if (!empty($setting['welcome_message_enabled']) && !empty($course['teacherIds'])) {
			$message = $this->getWelcomeMessageBody($user, $course);
	        $this->getMessageService()->sendMessage($course['teacherIds'][0], $user['id'], $message);
	    }

		$fields = array('studentNum'=> $this->getCourseStudentCount($courseId));
		$this->getCourseDao()->updateCourse($courseId, $fields);

		return $member;

	}

	private function getWelcomeMessageBody($user, $course)
    {
        $setting = $this->getSettingService()->get('course', array());
        $valuesToBeReplace = array('{{nickname}}', '{{course}}');
        $valuesToReplace = array($user['nickname'], $course['title']);
        $welcomeMessageBody = str_replace($valuesToBeReplace, $valuesToReplace, $setting['welcome_message_body']);
        return $welcomeMessageBody;
    }

	public function removeStudent($courseId, $userId)
	{
		$course = $this->getCourse($courseId);
		if (empty($course)) {
			throw $this->createNotFoundException("课程(#${$courseId})不存在，退出课程失败。");
		}

		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
		if (empty($member) or ($member['role'] != 'student')) {
			throw $this->createServiceException("用户(#{$userId})不是课程(#{$courseId})的学员，退出课程失败。");
		}

		$this->getMemberDao()->deleteMember($member['id']);

		$this->getCourseDao()->updateCourse($courseId, array(
			'studentNum' => $this->getCourseStudentCount($courseId),
		));

		$this->getLogService()->info('course', 'remove_student', "课程《{$course['title']}》(#{$course['id']})，移除学员#{$member['id']}");
	}

	public function lockStudent($courseId, $userId)
	{
		$course = $this->getCourse($courseId);
		if (empty($course)) {
			throw $this->createNotFoundException("课程(#${$courseId})不存在，封锁学员失败。");
		}

		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
		if (empty($member) or ($member['role'] != 'student')) {
			throw $this->createServiceException("用户(#{$userId})不是课程(#{$courseId})的学员，封锁学员失败。");
		}

		if ($member['locked']) {
			return ;
		}

		$this->getMemberDao()->updateMember($member['id'], array('locked' => 1));
	}

	public function unlockStudent($courseId, $userId)
	{
		$course = $this->getCourse($courseId);
		if (empty($course)) {
			throw $this->createNotFoundException("课程(#${$courseId})不存在，封锁学员失败。");
		}

		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
		if (empty($member) or ($member['role'] != 'student')) {
			throw $this->createServiceException("用户(#{$userId})不是课程(#{$courseId})的学员，解封学员失败。");
		}

		if (empty($member['locked'])) {
			return ;
		}

		$this->getMemberDao()->updateMember($member['id'], array('locked' => 0));
	}

	public function increaseLessonQuizCount($lessonId){
	    $lesson = $this->getLessonDao()->getLesson($lessonId);
	    $lesson['quizNum'] += 1;
	    $this->getLessonDao()->updateLesson($lesson['id'],$lesson);

	}
	public function resetLessonQuizCount($lessonId,$count){
	    $lesson = $this->getLessonDao()->getLesson($lessonId);
	    $lesson['quizNum'] = $count;
	    $this->getLessonDao()->updateLesson($lesson['id'],$lesson);
	}
	
	public function increaseLessonMaterialCount($lessonId){
	    $lesson = $this->getLessonDao()->getLesson($lessonId);
	    $lesson['materialNum'] += 1;
	    $this->getLessonDao()->updateLesson($lesson['id'],$lesson);

	}
	public function resetLessonMaterialCount($lessonId,$count){
	    $lesson = $this->getLessonDao()->getLesson($lessonId);
	    $lesson['materialNum'] = $count;
	    $this->getLessonDao()->updateLesson($lesson['id'],$lesson);
	}

	public function setMemberNoteNumber($courseId, $userId, $number)
	{
		$member = $this->getCourseMember($courseId, $userId);
		if (empty($member)) {
			return false;
		}

		$this->getMemberDao()->updateMember($member['id'], array(
			'noteNum' => (int) $number,
			'noteLastUpdateTime' => time(),
		));

		return true;
	}


	/**
	 * @todo refactor it.
	 */
	public function tryManageCourse($courseId)
	{
		$user = $this->getCurrentUser();
		if (!$user->isLogin()) {
			throw $this->createAccessDeniedException('未登录用户，无权操作！');
		}

		$course = $this->getCourseDao()->getCourse($courseId);
		if (empty($course)) {
			throw $this->createNotFoundException();
		}

		if (!$this->hasCourseManagerRole($courseId, $user['id'])) {
			throw $this->createAccessDeniedException('您不是课程的教师或管理员，无权操作！');
		}

		return CourseSerialize::unserialize($course);
	}

	public function tryAdminCourse($courseId)
	{
		$course = $this->getCourseDao()->getCourse($courseId);
		if (empty($course)) {
			throw $this->createNotFoundException();
		}

		$user = $this->getCurrentUser();
		if (empty($user->id)) {
			throw $this->createAccessDeniedException('未登录用户，无权操作！');
		}

		if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) == 0) {
			throw $this->createAccessDeniedException('您不是管理员，无权操作！');
		}

		return CourseSerialize::unserialize($course);
	}

	public function canManageCourse($courseId)
	{
		$user = $this->getCurrentUser();
		if (!$user->isLogin()) {
			return false;
		}
		if ($user->isAdmin()) {
			return true;
		}

		$course = $this->getCourse($courseId);
		if (empty($course)) {
			return $user->isAdmin();
		}

		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $user->id);
		if ($member and ($member['role'] == 'teacher')) {
			return true;
		}

		return false;
	}


	public function tryTakeCourse($courseId)
	{
		$course = $this->getCourse($courseId);
		if (empty($course)) {
			throw $this->createNotFoundException();
		}
		$user = $this->getCurrentUser();
		if (!$user->isLogin()) {
			throw $this->createAccessDeniedException('您尚未登录用户，请登录后再查看！');
		}

		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $user['id']);
		if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
			return array($course, $member);
		}

		if (empty($member) or !in_array($member['role'], array('teacher', 'student'))) {
			throw $this->createAccessDeniedException('您不是课程学员，不能查看课程内容，请先购买课程！');
		}

		return array($course, $member);
	}

	public function isMemberNonExpired($course, $member)
	{
		if (empty($course) or empty($member)) {
			throw $this->createServiceException("course, member参数不能为空");
		}

		if ($course['expiryDay'] == 0) {
			return true;
		}

		if ($member['deadline'] == 0) {
			return true;
		}

		if ($member['deadline'] > time()) {
			return true;
		}

		return false;
	}

	public function canTakeCourse($course)
	{
		$course = empty($course['id']) ? $this->getCourse(intval($course)) : $course;
		if (empty($course)) {
			return false;
		}

		$user = $this->getCurrentUser();
		if (!$user->isLogin()) {
			return false;
		}

		if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
			return true;
		}

		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($course['id'], $user['id']);
		if ($member and in_array($member['role'], array('teacher', 'student'))) {
			return true;
		}

		return false;
	}

	public function tryLearnCourse($courseId)
	{
		$course = $this->getCourseDao()->getCourse($courseId);
		if (empty($course)) {
			throw $this->createNotFoundException();
		}

		$user = $this->getCurrentUser();
		if (empty($user)) {
			throw $this->createAccessDeniedException('未登录用户，无权操作！');
		}

		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $user['id']);
		if (empty($member) or !in_array($member['role'], array('admin', 'teacher', 'student'))) {
			throw $this->createAccessDeniedException('您不是课程学员，不能学习！');
		}

		return array($course, $member);
	}

	public function getCourseAnnouncement($courseId, $id)
	{
		$announcement = $this->getAnnouncementDao()->getAnnouncement($id);
		if (empty($announcement) or $announcement['courseId'] != $courseId) {
			return null;
		}
		return $announcement;
	}

	public function findAnnouncements($courseId, $start, $limit)
	{
		return $this->getAnnouncementDao()->findAnnouncementsByCourseId($courseId, $start, $limit);
	}
	
	public function createAnnouncement($courseId, $fields)
	{
		$course = $this->tryManageCourse($courseId);
        if (!ArrayToolkit::requireds($fields, array('content'))) {
        	$this->createNotFoundException("课程公告数据不正确，创建失败。");
        }

        if(isset($fields['content'])){
        	$fields['content'] = $this->purifyHtml($fields['content']);
        }

		$announcement = array();
		$announcement['courseId'] = $course['id'];
		$announcement['content'] = $fields['content'];
		$announcement['userId'] = $this->getCurrentUser()->id;
		$announcement['createdTime'] = time();
		return $this->getAnnouncementDao()->addAnnouncement($announcement);
	}



	public function updateAnnouncement($courseId, $id, $fields)
	{
		$course = $this->tryManageCourse($courseId);

        $announcement = $this->getCourseAnnouncement($courseId, $id);
        if(empty($announcement)) {
        	$this->createNotFoundException("课程公告{$id}不存在。");
        }

        if (!ArrayToolkit::requireds($fields, array('content'))) {
        	$this->createNotFoundException("课程公告数据不正确，更新失败。");
        }
        
        if(isset($fields['content'])){
        	$fields['content'] = $this->purifyHtml($fields['content']);
        }

        return $this->getAnnouncementDao()->updateAnnouncement($id, array(
        	'content' => $fields['content']
    	));
	}

	public function deleteCourseAnnouncement($courseId, $id)
	{
		$course = $this->tryManageCourse($courseId);
		$announcement = $this->getCourseAnnouncement($courseId, $id);
		if(empty($announcement)) {
			$this->createNotFoundException("课程公告{$id}不存在。");
		}

		$this->getAnnouncementDao()->deleteAnnouncement($id);
	}

    private function getAnnouncementDao()
    {
    	return $this->createDao('Course.CourseAnnouncementDao');
    }

	private function hasCourseManagerRole($courseId, $userId) 
	{
		if($this->getUserService()->hasAdminRoles($userId)){
			return true;
		}

		$member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
		if ($member and ($member['role'] == 'teacher')) {
			return true;
		}

		return false;
	}

	private function isCurrentUser($userId){
		$user = $this->getCurrentUser();
		if($userId==$user->id){
			return true;
		}
		return false;
	}



    private function getCourseDao ()
    {
        return $this->createDao('Course.CourseDao');
    }

    private function getOrderDao ()
    {
        return $this->createDao('Course.OrderDao');
    }

    private function getFavoriteDao ()
    {
        return $this->createDao('Course.FavoriteDao');
    }

    private function getMemberDao ()
    {
        return $this->createDao('Course.CourseMemberDao');
    }

    private function getLessonDao ()
    {
        return $this->createDao('Course.LessonDao');
    }

    private function getLessonLearnDao ()
    {
        return $this->createDao('Course.LessonLearnDao');
    }

    private function getLessonViewedDao ()
    {
        return $this->createDao('Course.LessonViewedDao');
    }

    private function getChapterDao()
    {
        return $this->createDao('Course.CourseChapterDao');
    }

    private function getCategoryService()
    {
    	return $this->createService('Taxonomy.CategoryService');
    }

    private function getFileService()
    {
    	return $this->createService('Content.FileService');
    }

    private function getUserService()
    {
    	return $this->createService('User.UserService');
    }

    private function getReviewService()
    {
    	return $this->createService('Course.ReviewService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');        
    }

    private function getDiskService()
    {
        return $this->createService('User.DiskService');
    }


    private function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    private function getMessageService(){
        return $this->createService('User.MessageService');
    }

    private function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

}

class CourseSerialize
{
    public static function serialize(array &$course)
    {
    	if (isset($course['tags'])) {
    		if (is_array($course['tags']) and !empty($course['tags'])) {
    			$course['tags'] = '|' . implode('|', $course['tags']) . '|';
    		} else {
    			$course['tags'] = '';
    		}
    	}
    	
    	if (isset($course['goals'])) {
    		if (is_array($course['goals']) and !empty($course['goals'])) {
    			$course['goals'] = '|' . implode('|', $course['goals']) . '|';
    		} else {
    			$course['goals'] = '';
    		}
    	}

    	if (isset($course['audiences'])) {
    		if (is_array($course['audiences']) and !empty($course['audiences'])) {
    			$course['audiences'] = '|' . implode('|', $course['audiences']) . '|';
    		} else {
    			$course['audiences'] = '';
    		}
    	}

    	if (isset($course['teacherIds'])) {
    		if (is_array($course['teacherIds']) and !empty($course['teacherIds'])) {
    			$course['teacherIds'] = '|' . implode('|', $course['teacherIds']) . '|';
    		} else {
    			$course['teacherIds'] = null;
    		}
    	}

        return $course;
    }

    public static function unserialize(array $course = null)
    {
    	if (empty($course)) {
    		return $course;
    	}

		$course['tags'] = empty($course['tags']) ? array() : explode('|', trim($course['tags'], '|'));

		if(empty($course['goals'] )) {
			$course['goals'] = array();
		} else {
			$course['goals'] = explode('|', trim($course['goals'], '|'));
		}

		if(empty($course['audiences'] )) {
			$course['audiences'] = array();
		} else {
			$course['audiences'] = explode('|', trim($course['audiences'], '|'));
		}

		if(empty($course['teacherIds'] )) {
			$course['teacherIds'] = array();
		} else {
			$course['teacherIds'] = explode('|', trim($course['teacherIds'], '|'));
		}

		return $course;
    }

    public static function unserializes(array $courses)
    {
    	return array_map(function($course) {
    		return CourseSerialize::unserialize($course);
    	}, $courses);
    }
}



class LessonSerialize
{
    public static function serialize(array $lesson)
    {
        return $lesson;
    }

    public static function unserialize(array $lesson = null)
    {
        return $lesson;
    }

    public static function unserializes(array $lessons)
    {
    	return array_map(function($lesson) {
    		return LessonSerialize::unserialize($lesson);
    	}, $lessons);
    }
}