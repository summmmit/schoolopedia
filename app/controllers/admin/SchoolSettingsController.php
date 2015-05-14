<?php

/**
 * Description of AdminAccountController
 *
 * @author 1084760
 */
class SchoolSettingsController extends BaseController {

    protected $user;
    protected $schoolId;
    protected $schoolSessionId;

    public function __construct() {

        $this->user = Sentry::getUser();

        $this->schoolId = $this->user->school_id;
    }

    protected function getUser() {
        return $this->user;
    }

    protected function getSchoolId() {
        return $this->schoolId;
    }

    protected function getSchoolSessionId() {
        
        $session = SchoolSession::where('school_id', '=', $this->getschoolId())
                                ->where('current_session', '=', 1)
                                ->get()->first();
        
        return $this->schoolSessionId = $session->id;
    }
    
    public function postSetSchoolSessions() {
        $start_session_from = Input::get('start_session_from');
        $end_session_untill = Input::get('end_session_untill');
        $current_session = Input::get('current_session');

        $school_session = new SchoolSession();
        $school_session->session_start = $start_session_from;
        $school_session->session_end = $end_session_untill;
        $school_session->school_id = Sentry::getUser()->school_id;
        $school_session->current_session = ($current_session == "on") ? 1 : 0;

        $school_session->save();

        return Redirect::route('admin-home')->with('global', 'Thanks , You have set Current Session of The School');
    }

    public function getSchoolSessions() {
        $session = SchoolSession::where('school_id', '=', $this->getschoolId())->get();
    }

    //for the school schedules

    public function postSetSchoolSchedule() {

        $schedule_starts_from = Input::get('schedule_starts_from');
        $schedule_ends_untill = Input::get('schedule_ends_untill');
        $school_opening_time = Input::get('school_opening_time');
        $school_lunch_time = Input::get('school_lunch_time');
        $school_closing_time = Input::get('school_closing_time');

        $school_schedule = new SchoolSchedule();
        $school_schedule->start_from = $schedule_starts_from;
        $school_schedule->close_untill = $schedule_ends_untill;
        $school_schedule->opening_time = $school_opening_time;
        $school_schedule->lunch_time = $school_lunch_time;
        $school_schedule->closing_time = $school_closing_time;
        $school_schedule->school_id = $this->getSchoolId();
        $school_schedule->school_session_id = $this->getSchoolSessionId();

        if ($school_schedule->save()) {

            $response = array(
                'status' => 'OK',
                'result' => array(
                    'schedule' => $school_schedule,
                )
            );

            return Response::json($response);
        } else {

            $response = array(
                'status' => 'Error',
                'result' => array(
                    'schedule' => 'none',
                )
            );

            return Response::json($response);
        }
    }

    public function getSchoolSettings() {

        $school = Schools::find($this->getSchoolId());

        $schedule = SchoolSchedule::where('school_id', '=', $this->getschoolId())
                                   ->where('school_session_id', '=', $this->getSchoolSessionId())
                                   ->get();
        $sessions = SchoolSession::where('school_id', '=', $this->getschoolId())->get();
        $current_session = SchoolSession::where('school_id', '=', $this->getschoolId())
                                         ->where('current_session', '=', 1)->get()->first();
        
        return View::make('admin.school-settings')
                   ->with('schedules', $schedule)
                   ->with('sessions', $sessions)
                   ->with('school', $school)
                   ->with('current_session', $current_session);
    }

    public function postScheduleStartFrom() {

        $schedule = SchoolSchedule::find(Input::get('pk'));
        $schedule->opening_time = Input::get('value');

        if ($schedule->save()) {

            $response = array(
                'status' => 'OK',
                'msg' => 'Updated created successfully',
                'errors' => null,
                'result' => array(
                    'schedule' => $schedule,
                )
            );

            return Response::json($response);
        } else {

            $response = array(
                'status' => 'Error',
                'msg' => 'Not Updated',
                'errors' => null,
                'result' => array(
                    'schedule' => 'none',
                )
            );

            return Response::json($response);
        }
    }

    public function postScheduleLunchFrom() {

        $schedule = SchoolSchedule::find(Input::get('pk'));
        $schedule->lunch_time = Input::get('value');

        if ($schedule->save()) {

            $response = array(
                'status' => 'OK',
                'msg' => 'Updated created successfully',
                'errors' => null,
                'result' => array(
                    'schedule' => $schedule,
                )
            );

            return Response::json($response);
        } else {

            $response = array(
                'status' => 'Error',
                'msg' => 'Not Updated',
                'errors' => null,
                'result' => array(
                    'schedule' => 'none',
                )
            );

            return Response::json($response);
        }
    }

    public function postScheduleCloseFrom() {

        $schedule = SchoolSchedule::find(Input::get('pk'));
        $schedule->closing_time = Input::get('value');

        if ($schedule->save()) {

            $response = array(
                'status' => 'OK',
                'msg' => 'Updated created successfully',
                'errors' => null,
                'result' => array(
                    'schedule' => $schedule,
                )
            );

            return Response::json($response);
        } else {

            $response = array(
                'status' => 'Error',
                'msg' => 'Not Updated',
                'errors' => null,
                'result' => array(
                    'schedule' => 'none',
                )
            );

            return Response::json($response);
        }
    }

    public function getSchoolStudentDetails(){
        $query = "select * from users
                  join users_groups
                  on users.id=users_groups.user_id and users_groups.groups_id=? and users.school_id=?
                  join user_details
                  on users.id=user_details.user_id
                  join users_to_class
                  on users_to_class.user_id=users.id";
        $all_users = DB::select($query, array(2, $this->getSchoolId()));

        return View::make('admin.school-students')->with('users', $all_users);
    }

    public function getSchoolStudents(){
        $query = "select
                        users.id,
                        users.school_id,
                        user_details.username,
                        user_details.first_name,
                        user_details.last_name,
                        users_to_class.class_id,
                        users_to_class.section_id,
                        user_details.pic
                  from users
                  join users_groups
                  on users.id=users_groups.user_id and users_groups.groups_id=? and users.school_id=?
                  join user_details
                  on users.id=user_details.user_id
                  join users_to_class
                  on users_to_class.user_id=users.id";
        $all_school_users = DB::select($query, array(2, $this->getSchoolId()));
        $i = 0;
        foreach($all_school_users as $all_school_user){

            $class = Classes::find($all_school_user->class_id)->get()->first();
            $all_users[$i]['class_name'] = $class->class;
            $section = Sections::find($all_school_user->section_id);
            $all_users[$i]['section_name'] = $section->section_name;

            $all_users[$i]['username'] = $all_school_user->username;
            $all_users[$i]['first_name'] = $all_school_user->first_name;
            $all_users[$i]['last_name'] = $all_school_user->last_name;
            $all_users[$i]['pic'] = $all_school_user->pic;
            $all_users[$i]['id'] = $all_school_user->id;
            $all_users[$i]['school_id'] = $all_school_user->school_id;
            $i++;
        }

        return View::make('admin.school-students')->with('users', $all_users);
    }

    public function getSchoolTeachers(){

        $all_teachers = 1;
        return View::make('admin.school-teachers')->with('teachers', $all_teachers);
    }

    public function getSchoolEvents(){
        return View::make('admin.events');
    }
    /**
     * Ajax Api for getting event types
     */
    public function postGetEventTypes(){

        $school_id = $this->getSchoolId();
        $event_types = EventTypes::where('school_id', '=', $school_id)->get();

        $response = array(
            'status' => 'Success',
            'result' => array(
                'event_types' => $event_types,
            )
        );

        return Response::json($response);
    }
    /**
     * Ajax Api for getting event types
     */
    public function postCreateEvent(){

        $title = Input::get('title');
        $start = Input::get('start');
        $end   = Input::get('end');
        $allDay = Input::get('allDay');
        $category = Input::get('category');
        $content  = Input::get('content');
        $school_id = $this->getSchoolId();

        if($allDay == "true"){
            $allDay = 1;
        }else{
            $allDay = 0;
        }

        $event = new Events();
        $event->title = $title;
        $event->start = date($start);
        $event->end   = date($end);
        $event->allday = $allDay;
        $event->category = $category;
        $event->content  = $content;
        $event->school_id = $school_id;

        if($event->save()){

            $response = array(
                'status' => 'Success',
                'result' => array(
                    'events' => $event,
                )
            );

            return Response::json($response);
        }else{

            $response = array(
                'status' => 'Failed',
                'result' => array(
                    'events' => 'none',
                )
            );

            return Response::json($response);
        }
    }

    public function postGetEvent(){

        $school_id = $this->getSchoolId();

        $all_events = Events::where('school_id', '=', $school_id)->get();

        $i = 0;
        foreach($all_events as $all_event){
            $event_type_name = EventTypes::where('id', '=', $all_event->category)->get()->first();
            $all_events[$i++]['category'] = $event_type_name->event_type_name;
        }

        return Response::json($all_events);

    }
    
    public function getSchoolPeriods(){
        
        $school_schedule = SchoolSchedule::where('school_id', '=', $this->getSchoolId())->where('current_schedule', '=', 1)->get()->first();
        
        $periods = Periods::where('schedule_id', '=', $school_schedule->id)->get();
        return View::make('admin.school-periods')->with('periods', $periods);
    }

}
