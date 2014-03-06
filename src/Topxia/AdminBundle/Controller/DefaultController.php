<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class DefaultController extends BaseController
{

    public function popularCoursesAction(Request $request)
    {
        $dateType = $request->query->get('dateType');

        $map = array();
        $students = $this->getCourseService()->searchMember(array('date'=>$dateType, 'role'=>'student'), 0 , 10000);
        foreach ($students as $student) {
            if (empty($map[$student['courseId']])) {
                $map[$student['courseId']] = 1;
            } else {
                $map[$student['courseId']] ++;
            }
        }
        asort($map, SORT_NUMERIC);
        $map = array_slice($map, 0, 5, true);

        $courses = array();
        foreach ($map as $courseId => $studentNum) {
            $course = $this->getCourseService()->getCourse($courseId);
            $course['addedStudentNum'] = $studentNum;
            $course['addedMoney'] = 0;

            $orders = $this->getOrderService()->searchOrders(array('courseId'=>$courseId, 'status' => 'paid', 'date'=>$dateType), 'latest', 0, 10000);
            foreach ($orders as $id => $order) {
                $course['addedMoney'] += $order['price'];
            }

            $courses[] = $course;
        }

        return $this->render('TopxiaAdminBundle:Default:popular-courses-table.html.twig', array(
            'courses' => $courses
        ));
        
    }

    public function indexAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:Default:index.html.twig');
    }

    public function latestUsersBlockAction(Request $request)
    {
        $users = $this->getUserService()->searchUsers(array(), array('createdTime', 'DESC'), 0, 5);
        return $this->render('TopxiaAdminBundle:Default:latest-users-block.html.twig', array(
            'users'=>$users,
        ));
    }

    public function unsolvedQuestionsBlockAction(Request $request)
    {
        $questions = $this->getThreadService()->searchThreads(
            array('type' => 'question', 'postNum' => 0),
            'createdNotStick',
            0,5
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));
        $askers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

        $teacherIds = array();
        foreach (ArrayToolkit::column($courses, 'teacherIds') as $teacherId) {
             $teacherIds = array_merge($teacherIds,$teacherId);
        }
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);        

        return $this->render('TopxiaAdminBundle:Default:unsolved-questions-block.html.twig', array(
            'questions'=>$questions,
            'courses'=>$courses,
            'askers'=>$askers,
            'teachers'=>$teachers
        ));
    }

    public function latestPaidOrdersBlockAction(Request $request)
    {
        $orders = $this->getOrderService()->searchOrders(array('status'=>'paid'), 'latest', 0 , 5);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($orders, 'courseId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'userId'));
        
        return $this->render('TopxiaAdminBundle:Default:latest-paid-orders-block.html.twig', array(
            'orders'=>$orders,
            'users'=>$users,
            'courses'=>$courses
        ));
    }

    public function questionRemindTeachersAction(Request $request, $courseId, $questionId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $question = $this->getThreadService()->getThread($courseId, $questionId);
        $questionUrl = $this->generateUrl('course_thread_show', array('courseId'=>$course['id'], 'id'=> $question['id']), true);
        foreach ($course['teacherIds'] as $receiverId) {
            $result = $this->getNotificationService()->notify($receiverId, 'default',
                "课程《{$course['title']}》有新问题 <a href='{$questionUrl}' target='_blank'>{$question['title']}</a>，请及时回答。");
        }

        return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}
