<?php

namespace App\Helpers;

use App\Services\VoxService as ServicesVox;

use Request;
use Auth;

class Calendar {

    // From: https://www.startutorial.com/articles/view/how-to-build-a-web-calendar-in-php#sec1

    /**
     * Constructor
     */
    public function __construct(){     
        $this->naviHref = getLangUrl('polls-calendar-html');
    }
     
    /********************* PROPERTY ********************/  
    private $dayLabels = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");
    private $currentYear=0;
    private $currentMonth=0;
    private $currentDay=0;
    private $currentDate=null;
    private $daysInMonth=0;
    private $naviHref= null;
     
    /********************* PUBLIC **********************/  
        
    /**
    * print out the calendar
    */
    public function show() {
        $year  = null;
        $month = null;
         
        if(null==$year&&isset($_GET['year'])) {
            $year = $_GET['year'];
        } else if(null==$year){
            $year = date("Y",time());
        }          
         
        if(null==$month&&isset($_GET['month'])) {
            $month = $_GET['month'];
        } else if(null==$month){
            $month = date("m",time());
        }                  
         
        $this->currentYear=$year;
        $this->currentMonth=$month;
        $this->daysInMonth=$this->_daysInMonth($month,$year);
        
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
            $useragent=$_SERVER['HTTP_USER_AGENT'];

            if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
                $phone = true;
            } else {
                $phone = false;
            }
        } else {
            $phone = false;
        }

        $year = Request::input('year') ?? date('Y');
        $month = Request::input('month') ?? date('m');

        $daily_polls = ServicesVox::getDailyPollsByMonth(Auth::guard('web')->user(), Auth::guard('admin')->user(), false);
         
        $content='<div id="poll-calendar" class="'.($phone || (isset($_GET['list']) && $_GET['list']) ? 'list-calendar' : '').' '.(!$daily_polls ? 'no-events-calendar' : '').'">'.
            '<div class="box">'.
                $this->_createNavi().
            '</div>'.
            '<div class="box-content">';
                $content.='<table class="table-days-text"><thead><tr>'.$this->_createLabels().'</tr></thead></table>';

                if(!$daily_polls) {
                    $content.='<div class="no-events">No events to display</div>';
                }

                $content.='<table class="table-days"><tbody>';
                $weeksInMonth = $this->_weeksInMonth($month,$year);
                // Create weeks in a month
                for( $i=0; $i<$weeksInMonth; $i++ ){
                    
                    //Create days in a week
                    for($j=1;$j<=7;$j++){
                        $content.=$this->_showDay($i*7+$j, $phone,$daily_polls);
                    }
                }
                
                $content.='</tbody></table>';
    
            $content.='</div>';
                 
        $content.='</div>';
        return $content;   
    }
     
    /********************* PRIVATE **********************/ 
    /**
    * create the li element for ul
    */
    private function _showDay($cellNumber, $phone, $daily_polls){
         
        if($this->currentDay==0){
            $firstDayOfTheWeek = date('N',strtotime($this->currentYear.'-'.$this->currentMonth.'-01'));
                     
            if(intval($cellNumber) == intval($firstDayOfTheWeek)){
                $this->currentDay=1;
            }
        }
         
        if( ($this->currentDay!=0)&&($this->currentDay<=$this->daysInMonth) ){
            $this->currentDate = date('Y-m-d',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay)));
            $cellContent = $this->currentDay;
            $this->currentDay++;   
        } else{
            $this->currentDate =null;
            $cellContent=null;
        }

        $content = ($cellNumber%7==1? '<tr>' : '').
        '<td id="li-'.$this->currentDate.'" class="'.($cellNumber%7==1?' start ':($cellNumber%7==0?' end ':' ')).($cellContent==null?'mask':'').'">';
        
        if(!empty($daily_polls)) {

            foreach($daily_polls as $dp) {
                if($dp['day'] == $cellContent) {

                    $content .= '<a class="poll-day desktop-day '.($dp['closed'] || $dp['taken'] ? 'stats' : ($dp['to_take'] ? 'to-take' : '')).' '.(isset($dp['scheduled']) ? 'admin' : '').'" href="javascript:;" style="background-color: '.$dp['color'].'" poll-id="'.$dp['id'].'">'.
                        '<div class="poll-day-inner">'.
                        '<img class="poll-image" src="'.$dp['category_image'].'" width="28" heigth="28">'.
                        '<p class="poll-q">'.$dp['title'].'</p>';
                    
                    if($dp['closed']) {
                        $content .= '<img class="poll-stat-image" src="'.$dp['closed_image'].'">'.
                        '<p class="butn check-stat">Results</p>';
                    } else {
                        if($dp['to_take']) {
                            $content .= '<img class="poll-take-image" src="'.$dp['to_take_image'].'">'.
                            '<p class="butn answer">Answer</p>';
                        } else if($dp['taken']) {
                            $content .= '<img class="poll-stat-image" src="'.$dp['closed_image'].'">'.
                            '<img class="poll-taken-image" src="'.$dp['taken_image'].'">'.
                            '<p class="butn check-stat">Results</p>';
                        }
                    }

                    if (isset($dp['scheduled'])) {
                        $content .= '<img class="clock" src="'.url('img/clock.png').'">'.
                        '<p class="butn check-stat">Check</p>';
                    }
                        
                    $content .= '</div></a>';

                    $content .= '<div class="mobile-day">';
                        if($phone) {
                            $content .= '<div class="info-list">'.
                                '<span class="day-word">'.date('D',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay-1))).'</span>'.
                                '<span class="poll-full-date">'.date('d',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay-1))).
                            '</div>';
                        } else {
                            $content .= '<div class="info-list">'.
                                '<span class="day-word">'.date('l',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay-1))).'</span>'.
                                '<span class="poll-full-date">'.date('F j, Y',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay-1))).
                            '</div>';
                        }

                        $content .= '<a href="javascript:;" class="list-event '.($dp['closed'] || $dp['taken'] ? 'stats' : ($dp['to_take'] ? 'to-take' : '')).' '.(isset($dp['scheduled']) ? 'admin' : '').'" poll-id="'.$dp['id'].'" data-date="2021-03-01" style="background-color: '.$dp['color'].'">'.
                            '<img class="poll-image" src="'.$dp['category_image'].'" width="28" heigth="28">'.
                            $dp['title'];

                        if($dp['closed']) {
                            $content .= '<img class="poll-stat-image" src="'.$dp['closed_image'].'">';
                        } else {
                            if($dp['to_take']) {
                                $content .= '<img class="poll-take-image" src="'.$dp['to_take_image'].'">';
                            } else if($dp['taken']) {
                                $content .= '<img class="poll-stat-image" src="'.$dp['closed_image'].'">'.
                                '<img class="poll-taken-image" src="'.$dp['taken_image'].'">';
                            }
                        }

                        if (isset($dp['scheduled'])) {
                            $content .= '<img class="clock" src="'.url('img/clock.png').'">';
                        }
                            
                    $content .= '</a></div>';
                }
            }
        }
        
        $content .= '<span class="day-number">'.$cellContent.'</span>';

        $content .= '</td>'.
        ($cellNumber%7==0? '</tr>' : '');

        return $content;
                
    }
     
    /**
    * create navigation
    */
    private function _createNavi(){
         
        $nextMonth = $this->currentMonth==12?1:intval($this->currentMonth)+1;
        $nextYear = $this->currentMonth==12?intval($this->currentYear)+1:$this->currentYear;
        $preMonth = $this->currentMonth==1?12:intval($this->currentMonth)-1;
        $preYear = $this->currentMonth==1?intval($this->currentYear)-1:$this->currentYear;
        $is_this_month = $this->currentMonth == date('m') && $this->currentYear == date('Y');

        return
        '<div class="header">'.
            '<div class="prev-next-month">'.
                '<a class="prev ajax-url" href="'.$this->naviHref.'?month='.sprintf('%02d',$preMonth).'&year='.$preYear.'"><img src="'.url('img/prev-arrow.png').'" width="10" height="17"/></a>'.
                '<a class="next ajax-url" href="'.$this->naviHref.'?month='.sprintf("%02d", $nextMonth).'&year='.$nextYear.'"><img src="'.url('img/next-arrow.png').'" width="10" height="17"/></a>'.
                '<a class="today-button desktop-today '.($is_this_month ? 'disabled' : 'ajax-url').'" href="'.($is_this_month ? 'javascript:;' : $this->naviHref.'?month='.date('m').'&year='.date('Y')).'">today</a>'.
            '</div>'.
            '<h2>'.date('F Y',strtotime($this->currentYear.'-'.$this->currentMonth.'-1')).'</h2>'.
            '<div class="style-calendar">'.
                '<a href="javascript:;" class="chosen-month">month</a>'.
                '<a href="javascript:;" class="chosen-list">list</a>'.
                '<a class="today-button mobile-today '.($is_this_month ? 'disabled' : 'ajax-url').'" href="'.($is_this_month ? 'javascript:;' : $this->naviHref.'?month='.date('m').'&year='.date('Y')).'">today</a>'.
            '</div>'.
        '</div>';
    }
         
    /**
    * create calendar week labels
    */
    private function _createLabels(){    
        $content='';
         
        foreach($this->dayLabels as $index=>$label){
            $content.='<th class="'.($label==6?'end title':'start title').' title">'.$label.'</th>';
        }
         
        return $content;
    }
     
    /**
    * calculate number of weeks in a particular month
    */
    private function _weeksInMonth($month=null,$year=null){
         
        if( null==($year) ) {
            $year =  date("Y",time()); 
        }
         
        if(null==($month)) {
            $month = date("m",time());
        }
         
        // find number of days in this month
        $daysInMonths = $this->_daysInMonth($month,$year);
        $numOfweeks = ($daysInMonths%7==0?0:1) + intval($daysInMonths/7);
        $monthEndingDay= date('N',strtotime($year.'-'.$month.'-'.$daysInMonths));
        $monthStartDay = date('N',strtotime($year.'-'.$month.'-01'));
         
        if($monthEndingDay<$monthStartDay){
            $numOfweeks++;
        }
         
        return $numOfweeks;
    }
 
    /**
    * calculate number of days in a particular month
    */
    private function _daysInMonth($month=null,$year=null){
         
        if(null==($year)) {
            $year =  date("Y",time()); 
        }
 
        if(null==($month)) {
            $month = date("m",time());
        }
             
        return date('t',strtotime($year.'-'.$month.'-01'));
    }

}