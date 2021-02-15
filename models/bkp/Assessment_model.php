<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assessment_model extends CI_Model {

        
        public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
				 $this->load->database();
				 $this->load->library('Multipledb');
				 if (isset($this->session->user_id))
				{
					date_default_timezone_set($this->session->timezone);
				}
        }
		public function getacademicyearbyschoolid($sid)
		 {
			 
			$query = $this->db->query("select startdate,enddate,id from academic_year where id=(select academic_id from schools where id='".$sid."')order by id desc limit 1");
			return $query->result_array();
		 }

        public function checkUser($username,$password,$langid)
        {
			$query = $this->db->query('select *,(SELECT language_key FROM language_master WHERE ID="'.$langid.'") as languagekey, (select classname from class WHERE ID=a.grade_id) as gradename FROM users a WHERE username="'.$username.'" AND password=SHA1(CONCAT(salt1,"'.$password.'",salt2)) AND status=1 AND (SELECT school_id FROM school_admin WHERE school_id=a.sid AND active=1 and flag=1)');
			
			//echo $this->db->last_query(); exit;
			return $query->result();
        }
		
		public function chkprevioussession($userid,$gradeid,$section,$schoolid)
        {
			$query = $this->db->query('select count(lastupdate) as lastsessiondate from gamedata where gu_id = "'.$userid.'" and lastupdate = (select max(period_date) from schools_period_schedule_days where grade_id="'.$gradeid.'" and section="'.$section.'" and sid="'.$schoolid.'")');
			
			//echo $this->db->last_query(); exit;
			return $query->result_array();
        }
		
		
			public function islogin($username,$password,$idealtime)
        {	//echo 'select count(id) as islogin FROM users a WHERE username='.$username.' AND password=md5('.$password.') AND status=1 AND (SELECT school_id FROM school_admin WHERE school_id=a.sid AND active=1) AND  TIMESTAMPDIFF(MINUTE,last_active_datetime,NOW())<='.$idealtime.'';
		$innow=date("Y-m-d H:i:s");
			$query = $this->db->query('CALL IsLogin("'.$username.'","'.$password.'","'.$idealtime.'","","'.$innow.'")');
			mysqli_next_result($this->db->conn_id);
			//echo $this->db->last_query(); exit;
			return $query->result();
        }
		public function checkuserisactive($userid,$login_session_id)
        { 
			$query = $this->db->query('CALL checkuserisactive("'.$userid.'","'.$login_session_id.'","'.date("Y-m-d H:i:s").'")');
			mysqli_next_result($this->db->conn_id);
			//echo $this->db->last_query(); exit;
			return $query->result();
        }
		public function updateuserloginstatus($userid,$login_session_id)
        {	//echo 'CALL updateuserloginstatus("'.$userid.'","'.$login_session_id.'")';exit;
			$query = $this->db->query('CALL updateuserloginstatus("'.$userid.'","'.$login_session_id.'")');
			mysqli_next_result($this->db->conn_id);
			
        }	
		
		/* public function getdates($userid)
		 {
			 
		$query = $this->db->query("select AY.startdate,AY.enddate from users UG,academic_year AY where AY.id=UG.academicyear and UG.id='".$userid."' limit 0,1");
		//echo $this->db->last_query(); 
			return $query->result_array();
		 }	 */
		 
		  public function getbspireport($userid)
		 {
			 
		$query = $this->db->query("SELECT (AVG(`game_score`)) as gamescore ,gs_id , lastupdate,DATE_FORMAT(`lastupdate`,'%m') as playedMonth  FROM `game_reports` WHERE gs_id in (59,60,61,62,63) and gu_id='".$userid."' and  lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."'    group by gs_id , lastupdate");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }	
		 
		 
		 
		 public function languagechange($email)
		 {
		$query = $this->db->query("select * from language_master where status='Y' and ID in (select languageID from schools_language where schoolID=(select sid from users where username='".$email."' and status=1) and status='Y')");
		//echo $this->db->last_query(); exit;
			return $query->result();
		 }
		 public function update_loginDetails($userid,$session_id)
		 { 
		$query = $this->db->query("update users set pre_logindate = login_date,login_date = '".date("Y-m-d")."',login_count=login_count+1,session_id=".$session_id.",islogin=1,last_active_datetime='".date("Y-m-d H:i:s")."' WHERE id =".$userid);
		//echo $this->db->last_query(); exit;
		 }	

		public function getRandomGames($check_date_time,$game_plan_id,$game_grade,$school_id)
		 {
			 
			$query = $this->db->query("SELECT gid FROM rand_selection WHERE DATE(created_date) = '".$check_date_time."' AND gp_id = '".$game_plan_id."' AND grade_id = '".$game_grade."' AND school_id = '".$school_id."' and user_id='".$this->session->user_id."' GROUP BY school_id, gs_id ORDER BY gs_id ASC");
			//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function deleteSPLRandomGames($check_date_time,$game_plan_id,$game_grade,$school_id)
		 {
			$query = $this->db->query("delete FROM rand_selection WHERE gp_id = '".$game_plan_id."' AND grade_id = '".$game_grade."' AND school_id = '".$school_id."'");
		 }
		 public function getAssignGames($game_plan_id,$game_grade,$uid,$catid)
		 {
			 
		$query = $this->db->query("SELECT a.id, a.grade_id, d.skill_id FROM users AS a JOIN g_plans AS b ON a.gp_id = b.id AND b.id = '".$game_plan_id."' JOIN class_plan_game AS c ON b.id = c.plan_id AND b.grade_id = c.class_id JOIN class_skill_game AS d ON c.class_id = d.class_id AND c.game_id = d.game_id AND d.class_id = '".$game_grade."' JOIN category_skills AS e ON e.id = d.skill_id WHERE a.id = '".$uid."' AND e.category_id = '".$catid."' GROUP BY d.skill_id");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function getSkillsRandom($catid)
		 {
			 
		$query = $this->db->query("SELECT a.id AS category_id, b.id AS skill_id FROM g_category AS a JOIN category_skills AS b ON a.id = b.category_id WHERE a.id = '".$catid."'");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function getSK_SkillsRandom($uid)
		 {
			 
		//$query = $this->db->query("SELECT b.id AS skill_id  from category_skills AS b WHERE FIND_IN_SET ( b.id , ( select weakSkills from sk_user_game_list where 	userID='".$uid."' and status=0))");
		$query = $this->db->query("SELECT b.id AS skill_id,levelid from category_skills AS b 
join sk_user_game_list as suk on FIND_IN_SET (b.id ,suk.weakSkills) where suk.userID='".$uid."' and suk.status=0");
		
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function assignRandomGame($catid,$game_plan_id,$game_grade,$uid,$skill_id,$school_id)
		 {
			 
		$query = $this->db->query("SELECT j.id, e.skill_id, j.name AS skill_name, g.gid, g.gname FROM users AS a
				JOIN g_plans AS b ON a.gp_id = b.id AND b.id = '".$game_plan_id."' JOIN class AS c ON a.grade_id = c.id AND c.id = '".$game_grade."' JOIN class_plan_game AS d ON c.id = d.class_id AND d.plan_id = b.id JOIN class_skill_game AS e ON e.class_id = c.id AND  d.game_id = e.game_id JOIN category_skills AS j ON e.skill_id = j.id JOIN skl_class_plan AS f ON a.sid = f.school_id JOIN games AS g ON d.game_id = g.gid JOIN skl_class_plan AS h ON h.plan_id = b.id AND h.class_id = c.id AND h.school_id = '".$school_id."' WHERE a.id = '".$uid."' AND g.gc_id = '".$catid."' and j.id = '".$skill_id."' and g.gid not in (SELECT gid FROM rand_selection WHERE gp_id = '".$game_plan_id."' AND grade_id = '".$game_grade."' AND school_id = '".$school_id."' and user_id='".$this->session->user_id."' and gs_id = '".$skill_id."') and g.gid not in(243,283,23,65,100,146,140,179,186,226,266,307,233) GROUP BY g.gid ORDER BY RAND() LIMIT 1");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function assignSK_RandomGame($check_date_time,$game_plan_id,$game_grade,$uid,$skill_id,$school_id)
		 {
			 
		$query = $this->db->query("SELECT gid FROM sk_rand_selection WHERE DATE(created_date) = '".$check_date_time."'  AND userID = '".$uid."' AND grade_id = '".$game_grade."' AND school_id = '".$school_id."' AND gs_id='".$skill_id."' GROUP BY school_id, gs_id,gid order by gs_id");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function getAssignSK_RandomGame($check_date_time,$game_plan_id,$game_grade,$uid,$school_id)
		 {
			 
		$query = $this->db->query("SELECT gid,gp_id FROM sk_rand_selection WHERE DATE(created_date) = '".$check_date_time."' AND userID = '".$uid."' AND grade_id = '".$game_grade."' AND school_id = '".$school_id."' GROUP BY school_id, gs_id,gid order by gs_id");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function assignSK_assignGameCount($game_plan_id,$skill_id,$school_id,$levelid)
		 {
			 
		$query = $this->db->query("select * from sk_personalized_game where sk_planSkillCountID in (select ID from sk_plan_skillcount where school_ID='".$school_id."' and plan_ID='".$game_plan_id."') and skillID = '".$skill_id."' and level='".$levelid."' ");
			//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function getSK_randomGames($game_plan_id,$game_grade,$uid,$skill_id,$school_id,$assign_count,$cur_day_skills,$levelid)
		 {
			 
		$query = $this->db->query("SELECT g.skill_ID as skill_id,(select name from category_skills where id=g.skill_ID) as skill_name,g.name as gname,g.ID as gid,gp.plan_ID FROM sk_games g join sk_games_plan gp on g.ID=gp.sk_game_ID  join sk_personalized_game pg on gp.plan_ID=from_GradeID and level='".$levelid."' WHERE g.skill_ID='".$skill_id."' and gp.school_ID='".$school_id."' and  pg.skillID=g.skill_ID and pg.sk_planSkillCountID in (select ID from sk_plan_skillcount where school_ID='".$school_id."' and plan_ID='".$game_plan_id."' ) and g.ID not in (SELECT gid FROM sk_rand_selection  WHERE userID = '".$uid."' AND grade_id = '".$game_grade."'  AND school_id = '".$school_id."' and gs_id = '".$skill_id."') and g.game_masterID not in(243,283,23,65,100,146,140,179,186,226,266,307,233) order by rand() limit ".($assign_count-$cur_day_skills)." ;");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 } 
		 public function getSK_mindatePlay($game_plan_id,$game_grade,$uid,$skill_id,$school_id,$catid)
		 {
			 
		$query = $this->db->query("select min(created_date) as mindate from sk_rand_selection where gc_id = '".$catid."' AND userID = '".$uid."'  and gs_id = '".$skill_id."' and grade_id = '".$game_grade."'  and school_id = '".$school_id."'");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function deleteSK_OldGames($catid,$game_plan_id,$game_grade,$uid,$skill_id,$school_id,$mindate)
		 {
			 
		$query = $this->db->query("delete from sk_rand_selection where gc_id = '".$catid."' AND userID = '".$uid."'  and gs_id = '".$skill_id."' and  grade_id = '".$game_grade."'  and school_id = '".$school_id."'  and created_date='".$mindate."'");
		//echo $this->db->last_query(); exit;
			 
		 }
		 public function deleteRandomGames($catid,$game_plan_id,$game_grade,$uid,$skill_id,$school_id,$del_where)
		 {
			 
		$query = $this->db->query("delete from rand_selection where gc_id = '".$catid."' and gs_id = '".$skill_id."' and gp_id = '".$game_plan_id."' and grade_id = '".$game_grade."'  and school_id = '".$school_id."' and user_id='".$this->session->user_id."' ".$del_where);
		//echo $this->db->last_query(); exit;
			 
		 }
		 public function deleteSK_RandomGames($catid,$game_plan_id,$game_grade,$uid,$skill_id,$school_id,$del_where)
		 {
			 
		$query = $this->db->query("delete from sk_rand_selection where gc_id = '".$catid."' AND userID = '".$uid."'  and gs_id = '".$skill_id."' and  grade_id = '".$game_grade."'  and school_id = '".$school_id."' ".$del_where);
		//echo $this->db->last_query(); exit;
			 
		 }
		 public function insertRandomGames($catid,$game_plan_id,$game_grade,$skill_id,$school_id,$section,$gameid,$check_date_time)
		 { 
			$query = $this->db->query("INSERT INTO rand_selection SET gc_id = '".$catid."', gs_id = '".$skill_id."', gid = '".$gameid."', gp_id = '".$game_plan_id."', grade_id = '".$game_grade."', section = '".$section."', school_id = '".$school_id."',user_id='".$this->session->user_id."',created_date = '".$check_date_time."'"); 
		 } 
		 public function insertSK_RandomGames($catid,$game_plan_id,$game_grade,$uid,$skill_id,$school_id,$section,$gameid,$check_date_time)
		 {

		$query = $this->db->query("INSERT INTO sk_rand_selection SET userID='".$uid."', gc_id = '".$catid."', gs_id = '".$skill_id."', gid = ".$gameid.", gp_id = '".$game_plan_id."', grade_id = '".$game_grade."', section = '".$section."', school_id = '".$school_id."', created_date = '".$check_date_time."';");
		
			 
		 }
		 public function getActualGames($game_plan_id,$game_grade,$uid,$catid,$where)
		 {
		$query = $this->db->query("SELECT j.id, (select count(*) as tot_game_played from game_reports where gu_id = '".$uid."'  AND gc_id = '".$catid."' AND gs_id = e.skill_id AND gp_id = b.id AND lastupdate = '".date('Y-m-d')."') as tot_game_played ,(select ".$this->config->item('starslogic')."(game_score)  from game_reports where gu_id =  '".$uid."'  AND gc_id = '".$catid."' AND gs_id = e.skill_id AND gp_id = b.id AND lastupdate = '".date('Y-m-d')."') as tot_game_score , e.skill_id, j.name AS skill_name, g.gid, g.gname, g.img_path,g.game_html, j.icon 
		FROM users AS a
		JOIN g_plans AS b ON a.gp_id = b.id AND b.id = '".$game_plan_id."'
		JOIN class AS c ON a.grade_id = c.id AND c.id = '".$game_grade."'
		JOIN class_plan_game AS d ON c.id = d.class_id AND d.plan_id = b.id
		JOIN class_skill_game AS e ON e.class_id = c.id AND  d.game_id = e.game_id
		JOIN category_skills AS j ON e.skill_id = j.id 
		JOIN skl_class_plan AS f ON a.sid = f.school_id
		JOIN games AS g ON d.game_id = g.gid
		JOIN skl_class_plan AS h ON h.plan_id = b.id AND h.class_id = c.id AND h.school_id = '".$_SESSION['school_id']."'
		WHERE a.id = '".$uid."' AND g.gc_id = '".$catid."' $where
		GROUP BY skill_id,g.gid");
	//	echo $this->db->last_query(); exit;
			return $query->result_array();
		 }	 
		 public function getSK_ActualGames($game_plan_id,$game_grade,$uid,$catid,$where,$planwhere)
		 {
			$query = $this->db->query("SELECT  g.skill_ID as skill_id,game_html,(select count(*) as tot_game_played from sk_game_reports where gu_id = '".$uid."'  AND gc_id = '".$catid."' AND g_id = g.ID AND gp_id = '".$game_plan_id."' AND lastupdate = '".date('Y-m-d')."') as tot_game_played, (select times_count from sk_games_plan where school_ID='".$_SESSION['school_id']."'  and sk_game_ID=g.ID $planwhere) as playcount, (select name from category_skills where id=g.skill_ID) AS skill_name,(select class_id from class_plan_game where game_id=g.game_masterID $planwhere ) AS gamegrade,g.ID as gid, g.name as gname, g.image_path as img_path FROM sk_games AS g where 1 $where order by skill_id ");
			//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function getTrainCalendar($userid,$startdate,$enddate,$catid)
		 {
		$query = $this->db->query("select group_concat(distinct(lastupdate)) as updateDates  from game_reports WHERE gu_id = '".$userid."' and (lastupdate between '".$startdate."' and '".$enddate."')  and gc_id = '".$catid."'");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function getBSPI($userid)
		 {
			 
		$query = $this->db->query("SELECT (".$this->config->item('skilllogic')."(`game_score`)) as score ,gs_id , lastupdate FROM `game_reports` WHERE gs_id in (59,60,61,62,63) and gu_id='".$userid."' and (lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."')   group by gs_id , lastupdate");
	//	echo $this->db->last_query();  exit;
			return $query->result_array();
		 }
		
		 public function getleftbardata($userid)
		 {
			 
		$todaydate=date('Y-m-d');
		$query = $this->db->query("select avatarimage,fname,DATEDIFF('".date("Y-m-d")."', login_date) as noofdays,pre_logindate from users where status=1 and id='".$userid."'");
		//echo $this->db->last_query(); exit;
			return $query->result();
		 }			 
		 
		 public function getMyCurrentTrophies($userid)
		 {
			$query = $this->db->query("select cs.id as catid,cs.name as name ,(select sum(diamond) from popuptrophys pt where gu_id=".$userid." and pt.catid=cs.id) as diamond ,(select sum(gold) from popuptrophys pt where gu_id=".$userid." and pt.catid=cs.id) as gold ,(select sum(silver) from popuptrophys pt where gu_id=".$userid." and pt.catid=cs.id) as silver from category_skills cs where category_id=1 ");
			//echo $this->db->last_query(); exit;
			/* $query = $this->db->query("select cs.id as catid,cs.name as name ,0 as diamond ,0 as gold ,0 as silver from category_skills cs where category_id=1 "); */
			
			return $query->result_array();
		 }		

		public function getmyprofile($userid)
		 {
			 
		$query = $this->db->query("SELECT *,(SELECT school_name FROM schools WHERE id=us.sid) as schoolname  FROM users us where id='".$userid."'");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }			

		public function getplandetais($planid)
		 {
			 
		$query = $this->db->query("select * FROM g_plans where id='".$planid."'");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }	

		public function getgradedetais($gradeid)
		 {
			 
		$query = $this->db->query("select distinct(classname),id from class where id='".$gradeid."'");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }	

		public function updateprofile($sfname,$gender,$emailid,$dob,$fathername,$mothename,$address,$sphoneno,$id,$shpassword,$salt1,$salt2,$confirmpass,$targetPath,$ip)
		
		 {
			  if($targetPath==""){$avatarimage="avatarimage";}
			 else{$avatarimage="'".$targetPath."'";}
			 
			 if($shpassword !='' && $confirmpass!='')
{
	 //$newpassword=md5($newpass); 		 
			 $qry=$this->db->query("INSERT INTO password_history(userid, changed_on, newpwd, ip) VALUES('".$id."','".date("Y-m-d H:i:s")."','".$confirmpass."','".$ip."') ");			 
		
		$query = $this->db->query("update users set fname = '".$sfname."',password='".$shpassword."',salt1='".$salt1."',salt2='".$salt2."',father='".$fathername."',mother='".$mothename."',gender='".$gender."',email='".$emailid."',dob='".$dob."',mobile='".$sphoneno."',address='".$address."', avatarimage=$avatarimage where id = '".$id."'");
}
else{
	
	$query = $this->db->query("update users set fname = '".$sfname."',father='".$fathername."',mother='".$mothename."',gender='".$gender."',email='".$emailid."',dob='".$dob."',mobile='".$sphoneno."',address='".$address."', avatarimage=$avatarimage where id = '".$id."'");
	
}
		//echo $this->db->last_query(); exit;
			//return $query->result_array();
		 }


	   public function getgameplanid($userid)
		 {
			 
		$query = $this->db->query("select gp_id from users where id = '".$userid."'");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }

		public function getgamenames($userid,$pid)
		 {
			 
		$query = $this->db->query("SELECT a.gid,a.gname FROM  games a, game_reports b where a.gid=b.g_id and b.gu_id='".$userid."' and b.gp_id = '".$pid."'  and a.gc_id = 1 and (lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."') group by a.gid");
		//echo $this->db->last_query(); exit;
			return $query->result();
		 }

		public function getgamedetails($userid,$gameid,$pid)
		 {
			 
		$query = $this->db->query("select g_id, avg(game_score) as game_score,lastupdate from game_reports where gp_id = '".$pid."' and gu_id='".$userid."' and g_id='".$gameid."' AND (lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."') group by lastupdate  ORDER BY lastupdate DESC LIMIT 10");
		//echo $this->db->last_query(); 
			return $query->result_array();
		 }		 
		 public function insertone($userid,$cid,$sid,$pid,$gameid,$total_ques,$attempt_ques,$answer,$score,$a6,$a7,$a8,$a9,$lastup_date,$schedule_val)
		 {
			 //echo 'hello'; exit;
		$query = $this->db->query("insert into gamedata (gu_id,gc_id,gs_id,gp_id,g_id,total_question,attempt_question,answer,game_score,gtime,rtime,crtime,wrtime,lastupdate,Is_schedule) values('".$userid."','".$cid."','".$sid."','".$pid."','".$gameid."','".$total_ques."','".$attempt_ques."','".$answer."','".$score."','".$a6."','".$a7."','".$a8."','".$a9."','".$lastup_date."','".$schedule_val."')");
		//echo $this->db->last_query(); exit;
			 
		 }	 
		 public function insertone_SK($userid,$cid,$sid,$pid,$gameid,$total_ques,$attempt_ques,$answer,$score,$a6,$a7,$a8,$a9,$lastup_date)
		 {
			 //echo 'hello'; exit;
		$query = $this->db->query("insert into sk_gamedata (gu_id,gc_id,gs_id,gp_id,g_id,total_question,attempt_question,answer,game_score,gtime,rtime,crtime,wrtime,lastupdate) values('".$userid."','".$cid."','".$sid."','".$pid."','".$gameid."','".$total_ques."','".$attempt_ques."','".$answer."','".$score."','".$a6."','".$a7."','".$a8."','".$a9."','".$lastup_date."')");
		//echo $this->db->last_query(); exit;
			 
		 }	
		 
		 public function insertlang($gameid,$userid,$userlang,$skillkit)
		 {
			 //echo 'hello'; exit;
		$query = $this->db->query("INSERT INTO game_language_track( gameID, userID, languageID, skillkit, createddatetime) VALUES ('".$gameid."','".$userid."','".$userlang."','".$skillkit."','".date("Y-m-d H:i:s")."')");
		//echo $this->db->last_query(); exit;
			 
		 }	
		 
		  public function getresultGameDetails($userid,$gameid)
		 {
			 //echo 'hello'; exit;
			/* $query = $this->db->query("select AY.id as academicid, AY.startdate,AY.enddate,(select gs_id from games where gid='".$gameid."') as gameskillid from users UG,academic_year AY where AY.id=UG.academicyear and UG.id='".$userid."' limit 0,1"); */
			$query = $this->db->query("select (select gs_id from games where gid='".$gameid."') as gameskillid,(select count(gu_id) from game_reports where gu_id=".$userid." and g_id='".$gameid."' and lastupdate='".date("Y-m-d")."' and gs_id IN(59,60,61,62,63)) as playedgamescount");
			return $query->result_array();
		 }	
		 
		  public function insertthree($userid,$gameid,$acid,$lastup_date,$st)
		 {
			 //echo 'hello'; exit;
		$query = $this->db->query("insert into user_games (gu_id,played_game,last_update,date,status,academicyear) values('".$userid."','".$gameid."','".$lastup_date."','".$lastup_date."','".$st."','".$acid."')");
		 
		 }	
		 /* public function getacademicyear()
		 {
			 
		$query = $this->db->query("select startdate,enddate from academic_year where id=(select academic_id from schools where id=".$this->session->school_id.") order by id desc limit 1");
		//echo $this->db->last_query(); 
			return $query->result_array();
		 }	 */	 
		 
		public function getacademicmonths($startdate,$enddate)
		 { //echo ;exit;
			 
		$query = $this->db->query("select DATE_FORMAT(m1, '%m') as monthNumber,DATE_FORMAT(m1, '%Y') as yearNumber,DATE_FORMAT(m1, '%b') as monthName from (select ('".$startdate."' - INTERVAL DAYOFMONTH('".$startdate."')-1 DAY) +INTERVAL m MONTH as m1 from (select @rownum:=@rownum+1 as m from(select 1 union select 2 union select 3 union select 4) t1,(select 1 union select 2 union select 3 union select 4) t2,(select 1 union select 2 union select 3 union select 4) t3,(select 1 union select 2 union select 3 union select 4) t4,(select @rownum:=-1) t0) d1) d2 where m1<='".$enddate."' order by m1");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 
		 public function getskills()
		 {
			 
		$query = $this->db->query("select name,id from category_skills where category_id = 1 order by id");
		//echo $this->db->last_query(); 
			return $query->result_array();
		 }
		 
		 public function getcalendar($uid,$start_date,$last_date)
		 {
	
		$query = $this->db->query("SELECT id, date_format(lastupdate, '%d/%m/%Y') as created_date FROM game_reports WHERE gu_id = '".$uid."' and lastupdate between '".$start_date."' and '".$last_date."'  and gc_id = 1");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }	
		 
		 public function getskpreport($userid,$skillsid,$month)
		 {
	
		$query = $this->db->query("SELECT (AVG(`game_score`)) as gamescore ,gs_id , lastupdate,DATE_FORMAT(`lastupdate`,'%m') as playedMonth FROM `game_reports` WHERE gs_id in (".$skillsid.") and gu_id='".$userid."' and  lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' and DATE_FORMAT(lastupdate, '%Y-%m')=\"".$month."\"   group by gs_id , lastupdate");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
		 public function mybspicalendar($school_id,$uid,$dateQry,$startdate,$enddate)
		 {
			 $query = $this->db->query('select (sum(a.game_score)/5) as game_score, lastupdate,playedDate from
								(
									SELECT '.$this->config->item('skilllogic').'(gr.game_score) as game_score,count(*) as cnt, lastupdate,DATE_FORMAT(`lastupdate`,"%d") as playedDate FROM game_reports gr join category_skills sk join users u WHERE gr.gu_id = u.id and u.sid = '.$school_id.' and gr.gu_id='.$uid.' and sk.id = gr.gs_id and gr.gs_id in (SELECT id FROM category_skills where category_id=1) and  lastupdate between "'.$startdate.'" and "'.$enddate.'" AND DATE_FORMAT(lastupdate, "%Y-%m")=\''.$dateQry.'\' group by lastupdate, gr.gs_id, gr.gu_id order by gr.gs_id
								) a group by lastupdate');
								//echo $this->db->last_query(); exit;
								return $query->result_array();
								
		 }

		 public function mybspicalendarSkillChart($skillsid,$uid,$dateQry)
		 {
			  $query = $this->db->query("select AVG(gamescore) as gamescore,gs_id,playedMonth from (SELECT (AVG(game_score)) as gamescore ,gs_id , lastupdate,DATE_FORMAT(lastupdate,'%m') as playedMonth  FROM game_reports WHERE gs_id in (59,60,61,62,63) and gu_id='".$uid."' and  lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' and DATE_FORMAT(lastupdate, '%Y-%m')=\"".$dateQry."\"   group by gs_id,lastupdate) a1 group by gs_id");
								//echo $this->db->last_query(); exit;
								return $query->result_array();
								
		 }
		   public function myTrophiesAll($userid,$startdate,$enddate)
		 {
			 $query = $this->db->query("select trophystar.gu_id AS gu_id,extract(month from trophystar.lastupdate) AS month,sum(trophystar.ct) as totstar,trophystar.id as category from trophystar where (trophystar.lastupdate>='".$startdate."' and trophystar.lastupdate<='".$enddate."') and  trophystar.gu_id='".$userid."' group by month,trophystar.id ");
								//echo $this->db->last_query(); exit;
								return $query->result_array();
								
		 }
		  
		 function getPlaysCountPrior($r)
		 {	
			 $query = $this->db->query("select playedGamesCount as max_playedGamesCount from (SELECT  (select count(distinct(lastupdate)) from game_reports gr where gr.gs_id=g.gs_id and gr.gu_id='".$r['id']."' and gr.g_id=cpg.game_id and lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."') as playedGamesCount FROM class_plan_game cpg join games g on g.gid=cpg.game_id WHERE cpg.class_id='".$r['grade_id']."' and cpg.plan_id='".$r['gp_id']."' and g.gs_id in (59,60,61,62,63) and cpg.game_id not in(243,283,23,65,100,146,140,179,186,226,266,307,233)) as a1 order by playedGamesCount ASC limit 1");
			//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }		 
		 	function getPlayCounts($r,$max_playedGamesCount)
		 {
			 $query = $this->db->query("select (select IFNULL(max(SessionID),0) from sk_user_game_list where userID='".$r['id']."' and planID='".$r['gp_id']."') as maxSession, (select skill_count from sk_plan_skillcount where school_ID='".$r['sid']."' and plan_ID='".$r['gp_id']."') as skillCount, (select count(*) FROM class_plan_game cpg join games g on g.gid=cpg.game_id WHERE cpg.class_id='".$r['grade_id']."' and cpg.plan_id='".$r['gp_id']."' and g.gs_id in (59,60,61,62,63) and g.gid not in(243,283,23,65,100,146,140,179,186,226,266,307,233) ) as acualGamesCount,count(*) as playedGamesCount  from (SELECT game_id,gs_id,(select count(distinct(lastupdate)) from game_reports gr where gr.gs_id=g.gs_id and gr.gu_id='".$r['id']."' and gr.g_id=cpg.game_id and lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' ) as playedGamesCount FROM class_plan_game cpg join games g on g.gid=cpg.game_id WHERE cpg.class_id='".$r['grade_id']."' and cpg.plan_id='".$r['gp_id']."' and g.gs_id in (59,60,61,62,63) and cpg.game_id not in(243,283,23,65,100,146,140,179,186,226,266,307,233)) as a1 where a1.playedGamesCount!=0 and a1.playedGamesCount>=".$max_playedGamesCount."");
			//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }		 
		 	function getSKBspi($r)
		 {
			 $query = $this->db->query("select ROUND(avg(gamescore),2) as gamescore,gs_id from (select avg(gamescore) as gamescore ,playedMonth,gs_id from ( SELECT (AVG(`game_score`)) as gamescore ,gs_id , lastupdate,DATE_FORMAT(`lastupdate`,'%m') as playedMonth  FROM `game_reports` WHERE gs_id in (59,60,61,62,63) and gu_id='".$r['id']."' and  lastupdate between '".$this->session->astartdate."'and '".$this->session->aenddate."' group by gs_id , lastupdate) as a1 group by gs_id,playedMonth) as a2 group by gs_id order by gamescore asc");
			//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }	
		  public function updateSKGameList($r)
		 {
			 //echo 'hello'; exit;
		$query = $this->db->query("update sk_user_game_list set status=1 where userID='".$r['id']."' and planID='".$r['gp_id']."'");
		//echo $this->db->last_query(); exit;
			 
		 }	
		  public function insertSKGameList($r,$maxsession,$month_array_skill,$levelid)
		 {
			 //echo 'hello'; exit;

		$query = $this->db->query("insert into sk_user_game_list(userID,planID,SessionID,weakSkills,levelid,status,created_date) values ('".$r['id']."','".$r['gp_id']."','".($maxsession)."','".implode (",", $month_array_skill)."','".implode (",", $levelid)."',0,'".date("Y-m-d")."')");
		//echo $this->db->last_query(); exit;
			 
		 }	
		 	 
		 
		 public function getbspireport1($userid,$mnths)
		 {
	
		$query = $this->db->query("SELECT (".$this->config->item('skilllogic')."(`game_score`)) as gamescore,gs_id , lastupdate FROM `game_reports` WHERE gs_id in (59,60,61,62,63) and gu_id='".$userid."' and DATE_FORMAT(lastupdate,'%b-%Y') in (".$mnths.") and  date(lastupdate) between '".$this->session->astartdate."' and '".$this->session->aenddate."' group by gs_id , lastupdate");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }
	  
		public function getstudentplay($userid,$txtFDate,$txtTDate)
		 {
	
		$query = $this->db->query("SELECT count(*) as PalyCount,lastupdate,gs_id ,
		(select concat(fname,' ',lname) from users u where u.id='".$userid ."') as Name,
		(select username from users u where u.id='".$userid ."') as Username,
		(select concat((select classname from class c where c.id=u.grade_id),' - ',section) from users u where u.id='".$userid ."') as Class from game_reports where gu_id='".$userid ."' and  date(lastupdate) between '".$this->session->astartdate."' and '".$this->session->aenddate."' and  date(lastupdate) between '".$txtFDate."' and '".$txtTDate."' group by date(lastupdate),gs_id order by date(lastupdate)");
		//echo $this->db->last_query(); exit;
			return $query->result_array();
		 }

	public function insertsparkies($arrofinput)	 
	{  //echo "<pre>";print_r($arrofinput);exit;
		/* $stored_procedure = "CALL insertsparkies(?,?,?,?,?,?,?,?,?) ";
		$result = $this->db->query($sp,$arrofinput); 	 */
		$inDatetime=date("Y-m-d H:i:s");
		$inDate=date("Y-m-d");
		$inTime=date('H:i:s');
	
		$query = $this->db->query("CALL insertsparkiesnew(".$arrofinput['inSID'].",".$arrofinput['inGID'].",".$arrofinput['inUID'].",'".$arrofinput['inScenarioCode']."','".$arrofinput['inTotal_Ques']."','".$arrofinput['inAttempt_Ques']."','".$arrofinput['inAnswer']."','".$arrofinput['inGame_Score']."','".$arrofinput['inPlanid']."','".$arrofinput['inGameid']."','".$inDatetime."','".$inDate."','".$inTime."')");
		mysqli_next_result($this->db->conn_id);
		return $query->result_array();
	}
	public function insertnewsfeeddata($arrofinput)	 
	{
		$inDatetime=date("Y-m-d H:i:s");
		$inDate=date("Y-m-d");
		
		$query = $this->db->query("CALL insertnewsfeeddata('".$arrofinput['inSID']."','".$arrofinput['inGID']."','".$arrofinput['inUID']."','".$arrofinput['inScenarioCode']."','".$arrofinput['inTotal_Ques']."','".$arrofinput['inAttempt_Ques']."','".$arrofinput['inAnswer']."','".$arrofinput['inGame_Score']."','".$arrofinput['inPlanid']."','".$arrofinput['inGameid']."','".$inDatetime."','".$inDate."')");
		
		//return $query->result_array();
	}
	
	public function getMyCurrentSparkies($school_id,$grade_id,$userid,$startdate,$enddate)
    {
		
		$query = $this->db->query("CALL getMyCurrentSparkies(".$school_id.",".$grade_id.",".$userid.",'".$startdate."','".$enddate."')");
		return $query->result_array();

	}	
	public function getNewsFeed($school_id,$grade_id,$userid,$type,$page,$startdate,$enddate)
	{	//echo "CALL getNewsFeed(".$school_id.",".$grade_id.",".$userid.",'".$type."')";exit;
		$query = $this->db->query("CALL getNewsFeed(".$school_id.",".$grade_id.",".$userid.",'".$type."','".$startdate."','".$enddate."')");
		mysqli_next_result($this->db->conn_id);
		return $query->result_array();
	}
	public function getNewsFeedCount($school_id,$grade_id,$userid,$type,$page,$startdate,$enddate)
	{		//echo "CALL getNewsFeedCount(".$school_id.",".$grade_id.",".$userid.",'".$type."')";exit;
		$inDate=date("Y-m-d");
			$query = $this->db->query("CALL getNewsFeedCount(".$school_id.",".$grade_id.",".$userid.",'".$type."','".$startdate."','".$enddate."','".$inDate."')");
			mysqli_next_result($this->db->conn_id);
			return $query->result_array();
	}	
	
	public function getTopSparkiesValue($startdate,$enddate,$school_ID,$grad_ID)
	{ 	
		
		$query = $this->db->query("select U_ID,points,monthName,monthNumber,S_ID,G_ID,group_concat(studentname) as username from (select U_ID,points,monthName,monthNumber,S_ID,G_ID,(select username from `users` where id = U_ID) as username,(select CONCAT(fname,' ',lname) from `users` where id = U_ID) as studentname from (select `a2`.`U_ID` AS `U_ID`,sum(`a2`.`Points`) AS `points`,date_format(`a2`.`Datetime`,'%b') AS `monthName`,date_format(`a2`.`Datetime`,'%m') AS `monthNumber`,a2.S_ID,a2.G_ID from user_sparkies_history `a2` where (date_format(`a2`.`Datetime`,'%Y-%m-%d') between '".$startdate."' and '".$enddate."')   group by date_format(`a2`.`Datetime`,'%m'),`a2`.`U_ID`) a1 where a1.U_ID in (select id from users where status=1 and visible=1) and a1.G_ID=".$grad_ID." and a1.S_ID=".$school_ID." and  a1.points=(select points from vv2 where vv2.monthNumber =a1.monthNumber  and vv2.monthNumber!=".date('m')." and vv2.G_ID=".$grad_ID." and vv2.S_ID=".$school_ID." ) ) as a5 group by monthNumber");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function getTopPlayedGames($startdate,$enddate,$school_ID,$grad_ID)
	{
		
		
		
		$query = $this->db->query("select countofplayed,gu_id,monthName,monthNumber,grad_ID,gs_ID,group_concat(studentname) as username  from (select countofplayed,gu_id,monthName,monthNumber,grad_ID,gs_ID,(select username from `users` where id = gu_id) as username,(select CONCAT(fname,' ',lname) from `users` where id = gu_id) as studentname from (select count(`gu_id`) AS `countofplayed`,`gu_id` AS `gu_id`,date_format(`lastupdate`,'%b') AS `monthName`,date_format(`lastupdate`,'%m') AS `monthNumber`,(select `sid` from `users` where (`id` = `gu_id`)) AS `gs_ID`,(select `grade_id` from `users` where (`id` = `gu_id`)) AS `grad_ID` from `game_reports` where (convert(date_format(`lastupdate`,'%Y-%m-%d') using latin1) between '".$this->session->astartdate."' and '".$this->session->aenddate."' ) group by date_format(`lastupdate`,'%m'),`gu_id`) a1 where a1.gu_id in (select id from users where status=1 and visible=1) and a1.grad_ID=".$grad_ID." and a1.gs_ID=".$school_ID." and a1.countofplayed in (select countofval from vi_gameplayed v where v.monthNumber=a1.monthNumber and v.monthNumber!=".date('m')." and  v.school_id=".$school_ID." and v.grad_id=".$grad_ID.") ) as a5 group by monthNumber");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function getTopBSPIScore($startdate,$enddate,$school_ID,$grad_ID)
	{ 	
		$query = $this->db->query("select bspi,monthName,monthNumber,sid,gu_id,grade_id,(select GROUP_CONCAT(CONCAT(fname,' ',lname)) from users where id = gu_id) as username,classname,school_name from(select bspi,monthName,monthNumber,sid,gu_id,grade_id,(select CONCAT(fname,' ',lname) from users where id = gu_id) as username,(select classname from class where id = grade_id)as classname,(select school_name from schools where id = sid)as school_name from(select finalscore as bspi,gu_id,monthNumber,monthName,sid,grade_id from vii_avguserbspiscorebymon ) as a1 where a1.gu_id in (select id from users where status=1 and visible=1) and a1.grade_id=".$grad_ID." and a1.sid=".$school_ID."  and ROUND(a1.bspi,2) in (select bspi from vii_topbspiscore as vv3 where vv3.monthNumber =a1.monthNumber and  vv3.monthNumber!=".date('m')." and vv3.grade_id=".$grad_ID." and vv3.sid=".$school_ID.")) as a5 group by monthNumber");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function getSuperAngels($startdate,$enddate,$school_ID,$grad_ID)
	{
		$query = $this->db->query("select ans,gu_id,monthName,monthNumber,grad_ID,gs_ID,group_concat(studentname) as username from (select ans,gu_id,monthName,monthNumber,grad_ID,gs_ID,(select username from `users` where id = gu_id) as username,(select CONCAT(fname,' ',lname) from `users` where id = gu_id) as studentname from (select sum(answer) as ans,`game_reports`.`gu_id` AS `gu_id`,date_format(`game_reports`.`lastupdate`,'%b') AS `monthName`,date_format(`game_reports`.`lastupdate`,'%m') AS `monthNumber`,(select `users`.`sid` from `users` where (`users`.`id` = `game_reports`.`gu_id`)) AS `gs_ID`,(select `users`.`grade_id` from `users` where (`users`.`id` = `game_reports`.`gu_id`)) AS `grad_ID` from `game_reports` where (convert(date_format(`game_reports`.`lastupdate`,'%Y-%m-%d') using latin1) between '".$this->session->astartdate."' and '".$this->session->aenddate."' ) group by date_format(`game_reports`.`lastupdate`,'%m'),`game_reports`.`gu_id`) a1 where a1.gu_id in (select id from users where status=1 and visible=1) and a1.grad_ID=".$grad_ID." and a1.gs_ID=".$school_ID." and a1.ans in (select ans from vii_topsuperangels v where v.monthNumber=a1.monthNumber and  v.monthNumber!=".date('m')." and v.gs_ID=".$school_ID." and v.grad_ID=".$grad_ID.")) as a5 group by monthNumber");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	
	/* Hari */
	
	public function getbadgeone($startdate,$enddate,$grad_ID,$userid,$school_ID)	 
	{
		$currentmonth = date('F');
		
		/* $query = $this->db->query("select gu_id,score,month,count(gu_id) as total from (select score, month, gu_id from (select (sum(score1)/5) as score,DATE_FORMAT(lastupdate, '%M') AS month,gu_id from (select AVG(score) as score1,lastupdate,gs_id,gu_id from (SELECT avg(game_score) as score,gu_id, gs_id,lastupdate from game_reports WHERE gs_id in (59,60,61,62,63) and gu_id in (select id from users where grade_id ='".$gradeid."' and sid='".$schoolid."') and lastupdate between '".$startdate."' AND '".$enddate."' GROUP by gu_id,gs_id,lastupdate) a1 GROUP by gu_id,gs_id,MONTH(lastupdate)) a2 group by gu_id,MONTH(lastupdate) order by score DESC) x1 group by month order by score DESC)x2 where gu_id='".$userid."' AND month!='".$currentmonth ."'"); */
		$query = $this->db->query("select bspi,monthName,monthNumber,sid,count(gu_id) as total,grade_id from(select finalscore as bspi,gu_id,monthNumber,monthName,sid,grade_id from vii_avguserbspiscorebymon) as a1 where a1.grade_id=".$grad_ID." and a1.sid=".$school_ID."  and a1.gu_id in(".$userid.") and ROUND(a1.bspi,2)=(select bspi from vii_topbspiscore  as vv3 where vv3.monthNumber =a1.monthNumber and  vv3.monthNumber!=".date('m')." and vv3.grade_id=".$grad_ID." and vv3.sid=".$school_ID.")");
		
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getbadgetwo($startdate,$enddate,$grad_ID,$userid,$school_ID)	 
	{
		$currentmonth = date('F');
		/* $query = $this->db->query("select gu_id,gameplayed,month,count(gu_id) as total from (SELECT count(gu_id) as gameplayed,DATE_FORMAT(lastupdate, '%M') AS month,gu_id from game_reports WHERE gs_id in (59,60,61,62,63) and gu_id in (select id from users where grade_id ='".$gradeid."' and sid='".$schoolid."') and lastupdate between '".$startdate."' AND '".$enddate."' GROUP by gu_id, MONTH(lastupdate) order by month,gameplayed DESC) x2 where gu_id='".$userid."'  AND month!='".$currentmonth ."'");  */
		$query = $this->db->query("select countofplayed,count(gu_id) as total,monthName,monthNumber,grad_ID,gs_ID from (select count(`gu_id`) AS `countofplayed`,`gu_id` AS `gu_id`,date_format(`lastupdate`,'%b') AS `monthName`,date_format(`lastupdate`,'%m') AS `monthNumber`,(select `sid` from `users` where (`id` = `gu_id`)) AS `gs_ID`,(select `grade_id` from `users` where (`id` = `gu_id`)) AS `grad_ID` from `game_reports` where (convert(date_format(`lastupdate`,'%Y-%m-%d') using latin1) between '".$this->session->astartdate."' and '".$this->session->aenddate."' ) group by date_format(`lastupdate`,'%m'),`gu_id`) a1 where a1.grad_ID=".$grad_ID." and a1.gs_ID=".$school_ID." and a1.gu_id in(".$userid.") and a1.countofplayed in (select countofval from vi_gameplayed v where v.monthNumber=a1.monthNumber and v.monthNumber!=".date('m')." and  v.school_id=".$school_ID." and v.grad_id=".$grad_ID.")");
		
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getbadgethree($startdate,$enddate,$gradeid,$userid,$schoolid)	 
	{
		$currentmonth = date('F');
		/* $query = $this->db->query("select  gu_id,ans,month,count(gu_id) as total from (select ans,month,gu_id from (SELECT SUM(answer) as ans,DATE_FORMAT(lastupdate, '%M') AS month,gu_id from game_reports WHERE gs_id IN (59,60,61,62,63) and gu_id IN (select id from users where grade_id ='".$gradeid."' and sid='".$schoolid."') and lastupdate between '".$startdate."' AND '".$enddate."' GROUP by gu_id, MONTH(lastupdate) order by month, ans DESC) x1 group by month order by ans DESC) x2 where gu_id='".$userid."'  AND month!='".$currentmonth ."' ") ; */
		$query = $this->db->query("select ans,count(gu_id) as total,monthName,monthNumber,grad_ID,gs_ID from (select sum(answer) as ans,`game_reports`.`gu_id` AS `gu_id`,date_format(`game_reports`.`lastupdate`,'%b') AS `monthName`,date_format(`game_reports`.`lastupdate`,'%m') AS `monthNumber`,(select `users`.`sid` from `users` where (`users`.`id` = `game_reports`.`gu_id`)) AS `gs_ID`,(select `users`.`grade_id` from `users` where (`users`.`id` = `game_reports`.`gu_id`)) AS `grad_ID` from `game_reports` where (convert(date_format(`game_reports`.`lastupdate`,'%Y-%m-%d') using latin1) between '".$this->session->astartdate."' and '".$this->session->aenddate."' ) group by date_format(`game_reports`.`lastupdate`,'%m'),`game_reports`.`gu_id`) a1 where a1.grad_ID=".$gradeid." and a1.gs_ID=".$schoolid." and a1.gu_id in(".$userid.") and a1.ans in (select ans from vii_topsuperangels v where v.monthNumber=a1.monthNumber and  v.monthNumber!=".date('m')." and v.gs_ID=".$schoolid." and v.grad_ID=".$gradeid.")");
		
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	
	public function getthemefile()
    {
		$query = $this->db->query("select * from thememaster where status = 1 ");
		return $query->result_array();
	}
	
	public function get_user_themefile($userid)
    {
		$query = $this->db->query("select usertheme from users where id = '".$userid."' ");
		return $query->result_array();
		 
	}
	
	public function updatethemefile($filename,$userid)
    {
	$query = $this->db->query("UPDATE users SET usertheme='".$filename."' where id = '".$userid."' ");
 
	}
	public function getOverallSparkyTopper($startdate,$enddate,$school_ID,$grad_ID)
	{ 	
		$query = $this->db->query("select U_ID,points,S_ID,G_ID,(select GROUP_CONCAT(studentname) from users where id = U_ID) as username,classname,GROUP_CONCAT(school_name) as school_name from 

(select U_ID,points,monthName,monthNumber,S_ID,G_ID,(select username from users where id = U_ID) as username,(select CONCAT(fname,' ',lname) from users where id = U_ID) as studentname,(select classname from class where id = G_ID)as classname,(select school_name from schools where id = S_ID)as school_name from 

(select a2.U_ID AS U_ID,sum(a2.Points) AS points,date_format(a2.Datetime,'%b') AS monthName,date_format(a2.Datetime,'%m') AS monthNumber,a2.S_ID,a2.G_ID from user_sparkies_history a2 where a2.S_ID in (select schools.id from schools where visible = 1) and a2.U_ID in (select id from users where status=1 and visible=1) group by a2.U_ID) a1 

where a1.points in (select points from vi_overallcrownytoppers as vv3 where a1.G_ID=vv3.G_ID )) as a5 group by classname");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function getOverallBspiTopper($startdate,$enddate,$school_ID,$grad_ID)
	{ 	
		$query = $this->db->query("select bspi,sid,gu_id,grade_id,(select GROUP_CONCAT(studentname) from users where id = gu_id) as username,classname,GROUP_CONCAT(school_name) as school_name from

(select bspi,sid,gu_id,grade_id,(select username from users where id = gu_id) as username,(select CONCAT(fname,' ',lname) from users where id = gu_id) as studentname,(select classname from class where id = grade_id)as classname,(select school_name from schools where id = sid)as school_name from

(select finalscore as bspi,v1.gu_id,v1.sid,v1.grade_id from vii_avguserbspiscore as v1
join users u on u.id = v1.gu_id where u.sid in (select id from schools where visible = 1) and u.status=1 and visible=1) as a1

 where a1.bspi in (select bspi from vii_overallbspitoppers as vv3 where a1.grade_id=vv3.grade_id )) as a5 group by classname");
	
		return $query->result_array();
	}
	 public function getClassPerformace_data($schoolid,$gradeid,$section,$tablename)
	{
 
		$query = $this->db->query("select rowNumber,id, name,lname,avatarimage,bspi from (select  (@cnt := @cnt + 1) AS rowNumber,id, fname as name,lname,avatarimage, IF(avgbspiset1 IS NULL,0,avgbspiset1) as bspi from (select id as id, fname,lname,avatarimage, grade_id,(select classname from class where id=grade_id) as gradename,a3.finalscore as avgbspiset1 from users mu  left join 
 (SELECT SUM(score)/5 as finalscore, gu_id, (SELECT sid from users where id=gu_id) as schoolid from (select (AVG(score)) as score, gu_id, gs_id from (SELECT (".$this->config->item('skilllogic')."(`game_score`)) as score , gs_id , gu_id, lastupdate FROM ".$tablename." join users as u on u.id=gu_id  WHERE gs_id in (59,60,61,62,63) and lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' and sid=".$schoolid." and grade_id='".$gradeid."' and section='".$section."' and status=1 and visible=1 group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id ) a2 group by gu_id) a3   on a3.gu_id=mu.id where sid=".$schoolid." and grade_id='".$gradeid."' and section='".$section."' and status=1 and visible=1  ORDER BY avgbspiset1 DESC ) as a5 CROSS JOIN (SELECT @cnt := 0) AS dummy) as b1 order by bspi asc");
		//echo $this->db->last_query(); 
		//exit;
			return $query->result_array();
	}
	public function GetBadgeData($school_id,$grade_id,$startdate,$enddate)
	{
		$query = $this->db->query("CALL GetBadgeData('".$school_id."','".$grade_id."','".$startdate."','".$enddate."','".date("Y-m-d H:i:s")."','".date("Y-m-d")."')");
		//return $query->result_array();
	}
	
	public function termsandcondition($terms,$username)
	{
		$query = $this->db->query("UPDATE users SET agreetermsandservice = '".$terms."' WHERE username='".$username."' ");
	}
	
	public function getMemoryRange()
	{
		$query = $this->db->query("select max(totalCount) as memory,CONCAT(startRange,'-',endRange) as rangeval from (select r.startRange,r.endRange,count(a5.gamescore) totalCount
from(select gamescore,gs_id,lastupdate,playedMonth,gu_id from (SELECT (AVG(game_score)) as gamescore,gs_id,lastupdate,DATE_FORMAT(lastupdate,'%m') as playedMonth,gu_id  FROM game_reports join users as u on u.id=gu_id WHERE gs_id in(59) and  lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' and sid='".$this->session->school_id."' and grade_id='".$this->session->game_grade."' group by gu_id,gs_id,lastupdate) as a1 where gamescore!=0 group by  gs_id,lastupdate order by gamescore ASC)a5 inner join range_values r on a5.gamescore >startrange  and a5.gamescore <=endrange group by r.startRange, r.endRange order by startRange asc )a2 group by totalCount order by totalCount desc limit 1");
		return $query->result_array();
	}
	public function getVisualProcessingRange()
	{
		$query = $this->db->query("select max(totalCount) as memory,CONCAT(startRange,'-',endRange) as rangeval from (select r.startRange,r.endRange,count(a5.gamescore) totalCount
from(select gamescore,gs_id,lastupdate,playedMonth,gu_id from (SELECT (AVG(game_score)) as gamescore,gs_id,lastupdate,DATE_FORMAT(lastupdate,'%m') as playedMonth,gu_id  FROM game_reports  join users as u on u.id=gu_id WHERE gs_id in(60) and  lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' and sid='".$this->session->school_id."' and grade_id='".$this->session->game_grade."' group by gu_id,gs_id,lastupdate) as a1 where gamescore!=0 group by  gs_id,lastupdate order by gamescore ASC)a5 inner join range_values r on a5.gamescore >startrange  and a5.gamescore <=endrange group by r.startRange, r.endRange order by startRange asc )a2 group by totalCount order by totalCount desc limit 1");
		return $query->result_array();
	}
	public function getFocusAttentionRange()
	{
		$query = $this->db->query("select max(totalCount) as memory,CONCAT(startRange,'-',endRange) as rangeval from (select r.startRange,r.endRange,count(a5.gamescore) totalCount
from(select gamescore,gs_id,lastupdate,playedMonth,gu_id from (SELECT (AVG(game_score)) as gamescore,gs_id,lastupdate,DATE_FORMAT(lastupdate,'%m') as playedMonth,gu_id  FROM game_reports  join users as u on u.id=gu_id WHERE gs_id in(61) and  lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' and sid='".$this->session->school_id."' and grade_id='".$this->session->game_grade."' group by gu_id,gs_id,lastupdate) as a1 where gamescore!=0 group by  gs_id,lastupdate order by gamescore ASC)a5 inner join range_values r on a5.gamescore >startrange  and a5.gamescore <=endrange group by r.startRange, r.endRange order by startRange asc )a2 group by totalCount order by totalCount desc limit 1");
		return $query->result_array();
	}
	public function getProblemSolvingRange()
	{
		$query = $this->db->query("select max(totalCount) as memory,CONCAT(startRange,'-',endRange) as rangeval from (select r.startRange,r.endRange,count(a5.gamescore) totalCount
from(select gamescore,gs_id,lastupdate,playedMonth,gu_id from (SELECT (AVG(game_score)) as gamescore,gs_id,lastupdate,DATE_FORMAT(lastupdate,'%m') as playedMonth,gu_id  FROM game_reports  join users as u on u.id=gu_id WHERE gs_id in(62) and  lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' and sid='".$this->session->school_id."' and grade_id='".$this->session->game_grade."' group by gu_id,gs_id,lastupdate) as a1 where gamescore!=0 group by  gs_id,lastupdate order by gamescore ASC)a5 inner join range_values r on a5.gamescore >startrange  and a5.gamescore <=endrange group by r.startRange, r.endRange order by startRange asc )a2 group by totalCount order by totalCount desc limit 1");
		return $query->result_array();
	}
	public function getLinguisticsRange()
	{
		$query = $this->db->query("select max(totalCount) as memory,CONCAT(startRange,'-',endRange) as rangeval from (select r.startRange,r.endRange,count(a5.gamescore) totalCount
from(select gamescore,gs_id,lastupdate,playedMonth,gu_id from (SELECT (AVG(game_score)) as gamescore,gs_id,lastupdate,DATE_FORMAT(lastupdate,'%m') as playedMonth,gu_id  FROM game_reports join users as u on u.id=gu_id WHERE gs_id in(63) and  lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' and sid='".$this->session->school_id."' and grade_id='".$this->session->game_grade."' group by gu_id,gs_id,lastupdate) as a1 where gamescore!=0 group by  gs_id,lastupdate order by gamescore ASC)a5 inner join range_values r on a5.gamescore >startrange  and a5.gamescore <=endrange group by r.startRange, r.endRange order by startRange asc )a2 group by totalCount order by totalCount desc limit 1");
		return $query->result_array();
	}
	public function checkbandwidthisexist($schoolID)
	{
		$query = $this->db->query("CALL checkbandwidthisexist(".$schoolID.",'".date("Y-m-d H:i:s")."')");
		mysqli_next_result($this->db->conn_id);
		return $query->result_array();
	}
	public function insertbandwidth($schoolID,$user_id,$Bps,$Kbps,$Mbps)
	{
		$query = $this->db->query("CALL insertbandwidth(".$schoolID.",".$user_id.",'".$Bps."','".$Kbps."','".$Mbps."','".date("Y-m-d H:i:s")."')");
		mysqli_next_result($this->db->conn_id);
	}
	public function getPlayedSkillCount($user_id)
	{
		$query = $this->db->query("SELECT COUNT(id),gs_id FROM `gamedata` WHERE gu_id =".$user_id." and lastupdate='".date("Y-m-d")."' group by gs_id ");
		return $query->result_array();
	}
	public function IsSkillkitExist($userid,$plan_id)
	{
		$query = $this->db->query("select count(ID) as isenable from sk_user_game_list where userID=".$userid." and planID=".$plan_id." ");
		return $query->result_array();
	}
	public function checkscheduledays($gradeid,$section,$schoolid)
	{
	$curdate=date('Y-m-d');
	$curday = date('l', strtotime($curdate)); 
	$gradecolumn = strtolower($curday).'_'."grade";
	$sectioncolumn = strtolower($curday).'_'."section";

	$query = $this->db->query("select case when count(*)>0 THEN 1 else 0 END as scheduleday from schools_period_schedule where academic_id=20 and school_id='".$schoolid."' and ".$gradecolumn." = (select REPLACE(classname,'Grade ','') from class where id ='".$gradeid."') and ".$sectioncolumn." = '".$section."' and status = 'Y' and school_id not in(select school_id from schools_leave_list where leave_date='".$curdate."' and school_id='".$schoolid."' )");
	//	 echo $this->db->last_query(); exit;
	return $query->result_array();
	} 

	 public function getgameid($gamename)
	{
		$query = $this->db->query("select gid from games where game_html='".$gamename."' ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function checkgame($gameid)
	{
		$curdate=date('Y-m-d');
		$schoolid = $this->session->school_id;
		$gradeid = $this->session->game_grade;
		$section = $this->session->section;
		
		$query = $this->db->query("select count(distinct gid) as gameid from rand_selection where gid='".$gameid."' and created_date='".$curdate."' and grade_id='".$gradeid."'  and school_id='".$schoolid."'  ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
		
	public function insert_login_log($userid,$sessionid,$ip,$country,$region,$city,$isp,$browser,$status)
		{
			$query = $this->db->query('INSERT INTO user_login_log(userid,sessionid,created_date,lastupdate,logout_date,ip,country,region,city,browser,isp,status)VALUES("'.$userid.'","'.$sessionid.'","'.date("Y-m-d H:i:s").'","'.date("Y-m-d H:i:s").'","'.date("Y-m-d H:i:s").'", "'.$ip.'","'.$country.'","'.$region.'","'.$city.'","'.$browser.'","'.$isp.'","'.$status.'")');
			return $query;
			
		}
 public function update_login_log($userid,$sessionid)
		{
			$query = $this->db->query('update user_login_log set lastupdate="'.date("Y-m-d H:i:s").'" where userid="'.$userid.'" and sessionid="'.$sessionid.'"');
			return $query;
			
		}
		public function update_logout_log($userid,$sessionid)
		{
			$query = $this->db->query('update user_login_log set lastupdate="'.date("Y-m-d H:i:s").'",logout_date="'.date("Y-m-d H:i:s").'" where userid="'.$userid.'" and sessionid="'.$sessionid.'"');
			return $query;
			
		}
		public function CheckSkillkitexist($userid)
		{
			$query = $this->db->query("select count(ID) as isavailable from sk_user_game_list where userID=".$userid." and DATE(created_date)='".date("Y-m-d")."'");
			return $query->result_array();
		}
		public function getUserPlayedDayscount($userid)
		{
			$query = $this->db->query("select count(distinct(lastupdate)) as playedDate from game_reports gr where gr.gu_id='".$userid."' and lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' ");
			return $query->result_array();
		}
	public function getConfigNoofDaysPlay()
	{
		$query = $this->db->query("select value from config_master where code='SKILLKIT_NODAYSPLAY' and status='Y'");
		return $query->result_array();
	}
	
	/*  PopUp Code Start */
	public function CheckisUser($username,$password)
	{
		$query = $this->db->query("select count(id) as isUser,id,sid,grade_id,(select classname from class where id=grade_id) as gradename,section,username,login_count,fname,lname FROM users a WHERE username='".$username."' AND password=SHA1(CONCAT(salt1,'".$password."',salt2)) AND status=1 and visible=1 AND (SELECT school_id FROM school_admin WHERE school_id=a.sid AND active=1 and flag=1)");
		return $query->result_array();
	}	
	public function CheckUserStatus($userid)
	{
		$query = $this->db->query("select count(id) as userstatus FROM isuser_log  WHERE User_id='".$userid."' and Confirmation_type='1' order by id DESC");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function CheckisScheduleday($gradename,$section,$sid)
	{	$curtime=date('H:i:s');
		$currentdaygrade=strtolower(date ('l')."_grade");$currentdaysection=strtolower(date ('l')."_section");
		$query = $this->db->query("SELECT count(schedule_id) as isschedule FROM schools_period_schedule WHERE ".$currentdaygrade."='".ltrim($gradename,'Grade ')."' and ".$currentdaysection."='".$section."' and school_id='".$sid."' and academic_id=20 and '".$curtime."' between start_time and end_time");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function insertuserlog($userid,$Login_type,$Confirmation_type)
	{	$curdateandtime=date('Y-m-d h:i:s');
		$this->db->query("INSERT into isuser_log(User_id,Login_type,Confirmation_type,Logged_datetime,Org_userid)values('".$userid."','".$Login_type."','".$Confirmation_type."','".$curdateandtime."','')");
		return $this->db->insert_id();
		//echo $this->db->last_query(); exit;
	}
	public function fetchrelateduser($userid,$username,$grade_id,$section,$sid)
	{	
		$query =$this->db->query("SELECT username,id FROM users WHERE username like '%".$username."%' and grade_id='".$grade_id."' and section='".$section."' and sid='".$sid."' ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	/*  PopUp Code end */
	public function getgameid_SK($gamename)
	{
		$query = $this->db->query("select ID as gid from sk_games where game_html='".$gamename."' ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	/* -------------------------------------------------------------------------------
		New Overall Topper Concept 
	*/
	public function InsertOverallSparkyToppernew()
	{
		$query = $this->db->query("INSERT into overalltoppers(userid,gradeid,sid,value,type,duedate,created_on)
		select U_ID,G_ID,S_ID,points,'CT','".date("Y-m-d")."','".date("Y-m-d H:i:s")."' from (select U_ID,points,monthName,monthNumber,S_ID,G_ID from 
(select a2.U_ID AS U_ID,sum(a2.Points) AS points,date_format(a2.Datetime,'%b') AS monthName,date_format(a2.Datetime,'%m') AS monthNumber,a2.S_ID,a2.G_ID from user_sparkies_history a2 where a2.S_ID in (select schools.id from schools where visible = 1) and a2.U_ID in (select id from users where status=1 and visible=1) group by a2.U_ID) a1
where a1.points in (select points from vi_overallcrownytoppers as vv3 where a1.G_ID=vv3.G_ID )) as a5 group by G_ID");
		//echo $this->db->last_query(); exit;
		
	}
	public function InsertOverallBspiToppernew()
	{
		$query = $this->db->query("INSERT into overalltoppers(userid,gradeid,sid,value,type,duedate,created_on) 
		select gu_id,grade_id,sid,bspi,'BT','".date("Y-m-d")."','".date("Y-m-d H:i:s")."' from (select bspi,sid,gu_id,grade_id from
(select finalscore as bspi,v1.gu_id,v1.sid,v1.grade_id from vii_avguserbspiscore as v1
join users u on u.id = v1.gu_id where u.sid in (select id from schools where visible = 1) and u.status=1 and visible=1) as a1
 where a1.bspi in (select bspi from vii_overallbspitoppers as vv3 where a1.grade_id=vv3.grade_id )) as a5 group by grade_id");
	
		
	}
	public function CheckTodaydataExist()
	{
		$query = $this->db->query("select count(id) as isexist from overalltoppers where duedate='".date("Y-m-d")."'");
		return $query->result_array();
	}
	public function ClearToppersData()
	{
		$query = $this->db->query("TRUNCATE TABLE overalltoppers");
		//return $query->result_array();
	}
	public function getOverallSparkyToppernew($startdate,$enddate,$school_ID,$grad_ID)
	{
		$query = $this->db->query("select GROUP_CONCAT(username) as username,GROUP_CONCAT(studentname) as studentname,classname,GROUP_CONCAT(school_name) as school_name,points from(Select gradeid,(select username from users where id=userid) as username,(select CONCAT(fname,' ',lname) from users where id=userid) as studentname,(select classname from class where id =gradeid)as classname,(select school_name from schools where id = sid)as school_name,value as points from overalltoppers where type='CT')c1 group by gradeid");
		return $query->result_array();
	}
	public function getOverallBspiToppernew($startdate,$enddate,$school_ID,$grad_ID)
	{
		$query = $this->db->query("select GROUP_CONCAT(username) as username,GROUP_CONCAT(studentname) as studentname,classname,GROUP_CONCAT(school_name) as school_name,bspi from(Select gradeid,(select username from users where id=userid) as username,(select CONCAT(fname,' ',lname) from users where id=userid) as studentname,(select classname from class where id =gradeid)as classname,(select school_name from schools where id = sid)as school_name,value as bspi from overalltoppers where type='BT')b1 group by gradeid");
		return $query->result_array();
	}
	public function Schools_Wise_Period_Insert($startdate,$enddate)
	{
		$query = $this->db->query("CALL Schools_Wise_Period_Insert('".$startdate."','".$enddate."')");
		mysqli_next_result($this->db->conn_id);
	}
/* ---------------------- Leader Board API Insert Start -----------------------*/
	public function CheckTodayLeaderboardDataExist($sid,$gid,$monthno)
	{
		$query = $this->db->query("select count(id) as isexist from leaderboard where year=YEAR('".date("Y-m-d")."') and monthnumber in(".$monthno.") and sid=".$sid." and gradeid=".$gid." ");
		return $query->result_array();
	}
	public function GetSchoolDetails()
	{
		$query = $this->db->query("select id,school_name,(select group_concat(class_id) from skl_class_plan where school_id=s.id) as grade from schools as s where status=1 and active=1 and visible=1");
		return $query->result_array();
	}
	public function InsertTopSparkiesValue($startdate,$enddate,$school_ID,$grad_ID)
	{
		$query = $this->db->query("INSERT into leaderboard(sid,gradeid,year,monthname,monthnumber,userid,value,type,Created_on)select S_ID,G_ID,Year,monthName,monthNumber,U_ID,points,'CB','".date("Y-m-d H:i:s")."' from (select U_ID,points,monthName,monthNumber,Year,S_ID,G_ID,(select username from users where id = U_ID) as username,(select CONCAT(fname,' ',lname) from users where id = U_ID) as studentname from (select a2.U_ID AS U_ID,sum(a2.Points) AS points,date_format(a2.Datetime,'%b') AS monthName,date_format(Datetime,'%Y') AS Year,date_format(a2.Datetime,'%m') AS monthNumber,a2.S_ID,a2.G_ID from user_sparkies_history a2 where (date_format(a2.Datetime,'%Y-%m-%d') between '".$startdate."' and '".$enddate."')   group by date_format(a2.Datetime,'%m'),a2.U_ID) a1 where a1.U_ID in (select id from users where status=1 and visible=1) and a1.G_ID=".$grad_ID." and a1.S_ID=".$school_ID." and  a1.points=(select points from vv2 where vv2.monthNumber =a1.monthNumber  and vv2.monthNumber!=".date('m')." and vv2.G_ID=".$grad_ID." and vv2.S_ID=".$school_ID." ) ) as a5 group by monthNumber");
		//echo $this->db->last_query(); exit;
		
	}
	public function InsertTopPlayedGames($startdate,$enddate,$school_ID,$grad_ID)
	{
		$query = $this->db->query("INSERT into leaderboard(sid,gradeid,year,monthname,monthnumber,userid,value,type,Created_on)select gs_ID,grad_ID,Year,monthName,monthNumber,group_concat(gu_id),countofplayed,'SGB','".date("Y-m-d H:i:s")."'  from (select countofplayed,gu_id,monthName,monthNumber,Year,grad_ID,gs_ID,(select username from users where id = gu_id) as username,(select CONCAT(fname,' ',lname) from users where id = gu_id) as studentname from (select count(gu_id) AS countofplayed,gu_id AS gu_id,date_format(lastupdate,'%b') AS monthName,date_format(lastupdate,'%m') AS monthNumber,date_format(lastupdate,'%Y') AS Year,(select sid from users where (id = gu_id)) AS gs_ID,(select grade_id from users where (id = gu_id)) AS grad_ID from game_reports where (convert(date_format(lastupdate,'%Y-%m-%d') using latin1) between '".$startdate."' and '".$enddate."' ) group by date_format(lastupdate,'%m'),gu_id) a1 where a1.gu_id in (select id from users where status=1 and visible=1) and a1.grad_ID=".$grad_ID." and a1.gs_ID=".$school_ID." and a1.countofplayed in (select countofval from vi_gameplayed v where v.monthNumber=a1.monthNumber and v.monthNumber!=".date('m')." and  v.school_id=".$school_ID." and v.grad_id=".$grad_ID.") ) as a5 group by monthNumber");
		//echo $this->db->last_query(); exit;
	}
	public function InsertTopBSPIScore($startdate,$enddate,$school_ID,$grad_ID)
	{ 	
		$query = $this->db->query("INSERT into leaderboard(sid,gradeid,year,monthname,monthnumber,userid,value,type,Created_on)select sid,grade_id,date_format('".$startdate."','%Y') AS Year,DATE_FORMAT(STR_TO_DATE(monthnumber, '%m'), '%b') as monthName,monthNumber,GROUP_CONCAT(gu_id),bspi,'SBB','".date("Y-m-d H:i:s")."' from
(select bspi,monthName,monthNumber,sid,gu_id,grade_id,(select CONCAT(fname,' ',lname) from users where id = gu_id) as username,(select classname from class where id = grade_id)as classname,(select school_name from schools where id = sid)as school_name from(select finalscore as bspi,gu_id,monthNumber,monthName,sid,grade_id from vii_avguserbspiscorebymon where monthNumber=DATE_FORMAT('".$startdate."', '%m')) as a1 where a1.gu_id in (select id from users where status=1 and visible=1) and a1.grade_id=".$grad_ID." and a1.sid=".$school_ID."  and ROUND(a1.bspi,2) in (select bspi from vii_topbspiscore as vv3 where vv3.monthNumber =a1.monthNumber and  vv3.monthNumber!=".date('m')." and vv3.grade_id=".$grad_ID." and vv3.sid=".$school_ID.")) as a5 group by monthNumber");
		//echo $this->db->last_query(); exit;
	}
	public function InsertSuperAngels($startdate,$enddate,$school_ID,$grad_ID)
	{
		$query = $this->db->query("INSERT into leaderboard(sid,gradeid,year,monthname,monthnumber,userid,value,type,Created_on)select gs_ID,grad_ID,Year,monthName,monthNumber,group_concat(gu_id),ans,'SAB','".date("Y-m-d H:i:s")."' as username from (select ans,gu_id,monthName,monthNumber,Year,grad_ID,gs_ID,(select username from users where id = gu_id) as username,(select CONCAT(fname,' ',lname) from users where id = gu_id) as studentname from 
(select sum(answer) as ans,game_reports.gu_id AS gu_id,date_format(game_reports.lastupdate,'%b') AS monthName,date_format(game_reports.lastupdate,'%m') AS monthNumber,date_format(game_reports.lastupdate,'%Y') AS Year,(select users.sid from users where (users.id = game_reports.gu_id)) AS gs_ID,(select users.grade_id from users where (users.id = game_reports.gu_id)) AS grad_ID from game_reports where (convert(date_format(game_reports.lastupdate,'%Y-%m-%d') using latin1) between '".$startdate."' and '".$enddate."' ) group by date_format(game_reports.lastupdate,'%m'),game_reports.gu_id) a1 where a1.gu_id in (select id from users where status=1 and visible=1) and a1.grad_ID=".$grad_ID." and a1.gs_ID=".$school_ID." and a1.ans in (select ans from vii_topsuperangels v where v.monthNumber=a1.monthNumber and  v.monthNumber!=".date('m')." and v.gs_ID=".$school_ID." and v.grad_ID=".$grad_ID.")) as a5 group by monthNumber");
		//echo $this->db->last_query(); exit;
	}
/*------------------- New Leader Board Concept Qry -------------------------*/
public function getTopBadge($schoolid,$gradid,$type)
{
	$query = $this->db->query("SELECT monthnumber,gradeid,group_concat(concat(fname,'',lname)) as username,type FROM leaderboard as l join users as u on find_in_set(u.id,userid) where l.sid='".$schoolid."' and l.gradeid='".$gradid."' and type='".$type."' group by l.id ");
	return $query->result_array();
}

public function getUserBadgeCount($sid,$gradeid,$userid)
{
	$query = $this->db->query("SELECT SUM(CASE WHEN type='SAB' THEN 1 else 0 END ) as sabbadge, SUM(CASE WHEN type='SBB' THEN 1 else 0 END ) as sbbbadge, SUM(CASE WHEN type='SGB' THEN 1 else 0 END ) as sgbbadge from leaderboard where gradeid=".$gradeid." and sid=".$sid." and userid=".$userid." ");
	return $query->result_array();
}
/*-------------------30 mins Time over concept-------------------------*/

	public function getSumofUserUsedTime($userid,$Todaydate)
	{
		$query =$this->db->query("SELECT SUM(TimeLoggedIn) as LoggedIntime from(SELECT TIMESTAMPDIFF(SECOND,created_date,lastupdate) AS TimeLoggedIn FROM user_login_log WHERE userid=".$userid." and date(created_date)='".$Todaydate."') as a2 ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	/* public function getMaxTimeofPlay()
	{
		$query = $this->db->query("select value from config_master where code='MAXTIMEOFPLAY' and status='Y'");
		return $query->result_array();
	} */
	public function getMaxTimeofPlay($sid)
	{
		$query = $this->db->query("select timer_value as value from schools where id='".$sid."' and status=1 and active=1 and flag=1");
		return $query->result_array();
	}
	public function TodayTimerInsert($userid)
	{
		$query = $this->db->query("Insert into userplaytime(userid,expiredon,expireddatetime)values(".$userid.",'".date("Y-m-d")."','".date("Y-m-d H:i:s")."')");
	}
	public function IsTotayTimerExist($userid)
	{
		$query = $this->db->query("Select count(id) as isexist from userplaytime where userid=".$userid." and expiredon='".date("Y-m-d")."'");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
/*-------------------30 mins Time over concept End------------------------*/

	public function getplayeddates($userid)
	{
		$query = $this->db->query("Select lastupdate as `date`,1 as badge from gamedata where gu_id='".$userid."' group by lastupdate");
	//	echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function curdayplaystatus($userid,$curday)
	{
		$query = $this->db->query("SELECT COUNT(DISTINCT gs_id) as total FROM `gamedata` WHERE `gu_id`='".$userid."' and `lastupdate`='".$curday."' and gs_id IN (59,60,61,62,63)");
	//	echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getfdbksubject()
	{
		$query = $this->db->query("SELECT `id`,`subject` FROM `feedback_subject_master` WHERE status='Y'");
	//	echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function userfdbk($userid,$qone,$qtwo,$qthree,$skillname,$usercmnt)
	{
		//$query = $this->db->query("INSERT INTO `users_feedback`(`userid`, `subjectid`, `comment`, `created_date`, `status`) VALUES ('".$userid."','".$subject."','".$comment."',NOW(),1)");
		
		$query = $this->db->query("INSERT INTO `users_feedback`(`userid`, `qone`, `qtwo`, `qthree`, `skillid`, `comment`, `created_date`, `status`) VALUES ('".$userid."','".$qone."','".$qtwo."','".$qthree."','".$skillname."','".$usercmnt."','".date("Y-m-d H:i:s")."',1)");
	//	echo $this->db->last_query(); exit;
		//return $query->result_array();
	}
	
	public function feedbackenable($userid)
	{
		$curmonth = date('m');
		
		$query = $this->db->query("SELECT count(userid) as uid, (select DATE_FORMAT(`creation_date`,'%m') from users where id='".$userid."') as regdate FROM `users_feedback` WHERE DATE_FORMAT(`created_date`,'%m')='".$curmonth."' and userid='".$userid."'");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
public function maxvaluesbbadge($premnthnumber,$schoolid,$gradeid)
{
	$query = $this->db->query("SELECT MAX(value) as sbtopvalue FROM `leaderboard` WHERE `sid`='".$schoolid."' and `monthnumber`='".$premnthnumber."' and gradeid='".$gradeid."' and `type`='SBB'");
	//echo $this->db->last_query(); exit;
	return $query->result_array();
}

public function sbbadge($userid,$premnthnumber,$schoolid,$gradeid,$topvalue)
{
	$query = $this->db->query("SELECT count(id) as isSB, value as usersbtopvalue FROM `leaderboard` WHERE `sid`='".$schoolid."' and `monthnumber`='".$premnthnumber."' and gradeid='".$gradeid."' and FIND_IN_SET('".$userid."',userid) and `type`='SBB' and value='".$topvalue."'  ");
	return $query->result_array();
}

public function maxvaluesabadge($premnthnumber,$schoolid,$gradeid)
{
	$query = $this->db->query("SELECT MAX(value) as satopvalue FROM `leaderboard` WHERE `sid`='".$schoolid."' and `monthnumber`='".$premnthnumber."' and gradeid='".$gradeid."' and `type`='SAB'");
	//echo $this->db->last_query(); exit;
	return $query->result_array();
}

public function sabadge($userid,$premnthnumber,$schoolid,$gradeid,$topvalue)
{
	$query = $this->db->query("SELECT count(id) as isSA, value as usersbtopvalue FROM `leaderboard` WHERE `sid`='".$schoolid."' and `monthnumber`='".$premnthnumber."' and gradeid='".$gradeid."' and FIND_IN_SET('".$userid."',userid) and `type`='SAB' and value='".$topvalue."' ");
	return $query->result_array();
}

public function maxvaluesgbadge($premnthnumber,$schoolid,$gradeid)
{
	$query = $this->db->query("SELECT MAX(value) as sgtopvalue FROM `leaderboard` WHERE `sid`='".$schoolid."' and `monthnumber`='".$premnthnumber."' and gradeid='".$gradeid."' and `type`='SGB'");
	//echo $this->db->last_query(); exit;
	return $query->result_array();
}

public function sgbadge($userid,$premnthnumber,$schoolid,$gradeid,$topvalue)
{
	$query = $this->db->query("SELECT count(id) as isSG,value as usersbtopvalue FROM `leaderboard` WHERE `sid`='".$schoolid."' and `monthnumber`='".$premnthnumber."' and gradeid='".$gradeid."' and FIND_IN_SET('".$userid."',userid) and `type`='SGB' and value='".$topvalue."' ");
	//echo $this->db->last_query(); exit;
	return $query->result_array();
}
public function userinfo($userid)
{
	$query = $this->db->query("select id, (select school_name from schools where id=u.sid) as sname, (select classname from class where id=u.grade_id) as classname,section from users u where id='".$userid."'");
	return $query->result_array();
}

	public function getTrainingCalendarData($userid,$curdate)
	{
		$query = $this->db->query("SELECT ROUND((SUM(gtime)/60),0) as MinutesTrained  , SUM(answer) as PuzzlesSolved, SUM(attempt_question) as PuzzlesAttempted,(select SUM(Points) from user_sparkies_history where U_ID='".$userid."' and date(Datetime)='".$curdate."') as Crownies FROM game_reports gr join users u on gr.gu_id=u.id	WHERE gtime IS NOT NULL AND answer IS NOT NULL and u.id='".$userid."' and lastupdate='".$curdate."' ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getonedaybspi($userid,$curdate)
	{
		$query = $this->db->query("SELECT SUM(avgskillscore)/5 as BSPI from (select MAX(game_score) as avgskillscore, gs_id FROM game_reports  where gu_id='".$userid."' and lastupdate='".$curdate."' group by gs_id) a1");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getonedayskillscore($userid,$curdate)
	{
		$query = $this->db->query("SELECT id, s1.mem as memory,s2.vp as visual, s3.fa as focus, s4.ps as problem,s5.lin as ling from users mu left join 

		(select AVG(game_score) as mem, gu_id FROM game_reports  where gu_id='".$userid."' and lastupdate='".$curdate."' and gs_id=59)s1 ON s1.gu_id=mu.id

		left join (select AVG(game_score) as vp, gu_id FROM game_reports  where gu_id='".$userid."' and lastupdate='".$curdate."' and gs_id=60)s2 ON s2.gu_id=mu.id

		left join (select AVG(game_score) as fa, gu_id FROM game_reports  where gu_id='".$userid."' and lastupdate='".$curdate."' and gs_id=61)s3 ON s3.gu_id=mu.id

		left join (select AVG(game_score) as ps, gu_id FROM game_reports  where gu_id='".$userid."' and lastupdate='".$curdate."' and gs_id=62)s4 ON s4.gu_id=mu.id

		left join (select AVG(game_score) as lin, gu_id FROM game_reports  where gu_id='".$userid."' and lastupdate='".$curdate."' and gs_id=63)s5 ON s5.gu_id=mu.id where mu.id='".$userid."' ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function overallskillscore($userid,$curdate)
	 {
		$query = $this->db->query("select AVG(gamescore) as skillscore,gs_id from (SELECT (AVG(game_score)) as gamescore ,gs_id , lastupdate  FROM game_reports WHERE gs_id in (59,60,61,62,63) and gu_id='".$userid."' and  lastupdate = '".$curdate."' group by gs_id,lastupdate) a1 group by gs_id");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
							
	 }
	
	public function getnxtsession($sid,$grade,$section,$curday,$nextdate)
	{
		$query = $this->db->query("select starttime,endtime,dayname1,selected_date,nameofday,grade,section,pd from (select * from 
( select monday_grade as grade,monday_section as section,start_time as starttime,end_time as endtime,period as pd,'Monday' as dayname1 from schools_period_schedule where school_id='".$sid."' and academic_id=20 and monday_grade!='' union select tuesday_grade,tuesday_section as section,start_time as starttime,end_time as endtime,period as pd,'Tuesday' as dayname1 from schools_period_schedule where school_id='".$sid."' and academic_id=20 and tuesday_grade!='' 
union select wednesday_grade,wednesday_section as section,start_time as starttime,end_time as endtime,period as pd,'Wednesday' as dayname1 from schools_period_schedule where school_id='".$sid."' and academic_id=20 and wednesday_grade!='' 
union select thursday_grade,thursday_section as section,start_time as starttime,end_time as endtime,period as pd,'Thursday' as dayname1 from schools_period_schedule where school_id='".$sid."' and academic_id=20 and thursday_grade!='' 
union select friday_grade,friday_section as section,start_time as starttime,end_time as endtime,period as pd,'Friday' as dayname1 from schools_period_schedule where school_id='".$sid."' and academic_id=20 and friday_grade!='' 
union select saturday_grade,saturday_section as section,start_time as starttime,end_time as endtime,period as pd,'Saturday' as dayname1 from schools_period_schedule where school_id='".$sid."' and academic_id=20 and saturday_grade!='' 
union select sunday_grade,sunday_section as section,start_time as starttime,end_time as endtime,period as pd,'Sunday' as dayname1  from schools_period_schedule where school_id='".$sid."' and academic_id=20 and sunday_grade!='')j1  cross join 
(select selected_date,dayname(selected_date) as nameofday from (select adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) selected_date from (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0, (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1, (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2, (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3, (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v where selected_date between '".$curday."' and '".$nextdate."'  and selected_date >= (select start_date from schools where id='".$sid."' and status=1 and active=1 and visible=1) and selected_date NOT IN (select leave_date from schools_leave_list where school_id = '".$sid."' and status=1)) j2 on j1.dayname1=j2.nameofday order by selected_date asc) x1 where concat(selected_date,'',starttime)>=date_format('".date("Y-m-d H:i:s")."', '%Y-%m-%d%H:%i') and grade='".$grade."' and section='".$section."'  group by pd order by selected_date ASC");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function get_clp_skillwise_avg($userid)		 
		{
			$query = $this->db->query("select id, fname, s1.skillscore_M as skillscorem, skillscore_V as skillscorev,skillscore_F as skillscoref,skillscore_P as skillscorep,skillscore_L as skillscorel, a3.finalscore as avgbspiset1 from users mu 
left join (SELECT SUM(score)/5 as finalscore, gu_id, (SELECT sid from users where id=gu_id) as schoolid from (select (AVG(score)) as score, gu_id, gs_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id in (59,60,61,62,63) and gu_id in (select id from users) group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id ) a2 group by gu_id) a3 on a3.gu_id=mu.id 

left join (select (AVG(score)) as skillscore_M, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =59 and gu_id='".$userid."' group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s1 on s1.gu_id=mu.id 
left join (select (AVG(score)) as skillscore_V, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =60 and gu_id='".$userid."' group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s2 on s2.gu_id=mu.id 
left join (select (AVG(score)) as skillscore_F, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =61 and gu_id='".$userid."' group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s3 on s3.gu_id=mu.id 
left join (select (AVG(score)) as skillscore_P, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =62 and gu_id='".$userid."' group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s4 on s4.gu_id=mu.id 
left join (select (AVG(score)) as skillscore_L, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =63 and gu_id='".$userid."' group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s5 on s5.gu_id=mu.id 

 where id='".$userid."' ORDER BY avgbspiset1 DESC");
 
			//echo $this->db->last_query(); exit;
			return $query->result_array();
		}
		
		public function getMonthWiseSkillScore($uid,$startdate,$enddate)
	{
		$query = $query = $this->db->query("select gs_id,(CASE WHEN gs_id=59 THEN 'MEMORY'
WHEN gs_id=60 THEN 'VP'
WHEN gs_id=61 THEN 'FA'
WHEN gs_id=62 THEN 'PS'
WHEN gs_id=63 THEN 'LI' else 0
END) as skillname,playedMonth,monthName,AVG(gamescore) as gamescore from (SELECT (AVG(game_score)) as gamescore ,gs_id , lastupdate,gu_id,DATE_FORMAT(lastupdate,'%m') as playedMonth,DATE_FORMAT(lastupdate, '%b') as monthName FROM game_reports WHERE gs_id in (59,60,61,62,63)and gu_id=".$uid." and lastupdate between '".$startdate."' and '".$enddate."' group by gs_id,lastupdate) a1 group by gs_id,playedMonth order by gs_id, lastupdate");
//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function getAsapBspi($username)
	{
		$query = $this->multipledb->db->query("SELECT  sum(game_score)/5 as bspi FROM game_reports join users as u on u.id=gu_id where u.username='".$username."' and u.status=1");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	public function getCLPScore($userid)		 
	{
		$query = $this->db->query("select id, fname, s1.skillscore_M as ME, skillscore_V as VP,skillscore_F as FA,skillscore_P as PS,skillscore_L as LI from users mu 
		
		left join (select (AVG(score)) as skillscore_M, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =59 and gu_id in (select id from users) group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s1 on s1.gu_id=mu.id 
		left join (select (AVG(score)) as skillscore_V, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =60 and gu_id in (select id from users) group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s2 on s2.gu_id=mu.id 
		left join (select (AVG(score)) as skillscore_F, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =61 and gu_id in (select id from users) group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s3 on s3.gu_id=mu.id 
		left join (select (AVG(score)) as skillscore_P, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =62 and gu_id in (select id from users) group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s4 on s4.gu_id=mu.id 
		left join (select (AVG(score)) as skillscore_L, gu_id from (SELECT (AVG(`game_score`)) as score , gs_id , gu_id, lastupdate FROM `game_reports` WHERE gs_id =63 and gu_id in (select id from users) group by gs_id , gu_id, lastupdate) a1 group by gs_id, gu_id) s5 on s5.gu_id=mu.id 

		where id='".$userid."' ");

		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getTotalTrainingData($userid)
	{
		$query = $this->db->query("SELECT ROUND((SUM(gtime)/60),0) as MinutesTrained  , SUM(answer) as PuzzlesSolved, SUM(attempt_question) as PuzzlesAttempted,(select SUM(Points) from user_sparkies_history where U_ID='".$userid."' and date(Datetime) between '".$this->session->astartdate."' and '".$this->session->aenddate."') as Crownies FROM game_reports gr join users u on gr.gu_id=u.id	WHERE gtime IS NOT NULL AND answer IS NOT NULL and u.id='".$userid."' and lastupdate between '".$this->session->astartdate."' and '".$this->session->aenddate."' ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getAsapcore($username)
	{
			$query = $this->multipledb->db->query("select 
			(select game_score from  game_reports where gs_id=59  and gu_id=a1.id ) as ME,
			(select game_score from  game_reports where gs_id=60  and gu_id=a1.id ) as VP,
			(select game_score from  game_reports where gs_id=61  and gu_id=a1.id ) as FA,
			(select game_score from  game_reports where gs_id=62  and gu_id=a1.id ) as PS,			
			(select game_score from  game_reports where gs_id=63  and gu_id=a1.id ) as LI			
			from (select id from users where username='".$username."' and status=1) a1 ");
			//echo $this->db->last_query(); exit;
			return $query->result_array();
	}
	
	public function chkportaltype($username,$password)
	{
		$query = $this->multipledb->db->query("select id,portal_type,sid,IS_ASAP,gp_id from users where username='".$username."' and password=SHA1(CONCAT(salt1,'".$password."',salt2)) and status=1");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function tuserfdbk($userid,$tqone,$tusercmnt)
	{
		//$query = $this->db->query("INSERT INTO `users_feedback`(`userid`, `subjectid`, `comment`, `created_date`, `status`) VALUES ('".$userid."','".$subject."','".$comment."',NOW(),1)");
		
		$query = $this->db->query("INSERT INTO `teachersday_feedback`(`userid`, `sid`,`qone`,`comment`, `created_date`, `status`) VALUES ('".$userid."','".$_SESSION['school_id']."','".$tqone."','".$tusercmnt."','".date("Y-m-d H:i:s")."',1)");
	//	echo $this->db->last_query(); exit;
		//return $query->result_array();
	}
	
	public function isuser_tfeedback($id)
	{
		$query = $this->db->query("select count(userid) as total from teachersday_feedback where userid='".$id."'");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	
	/*---------------------------- IAS Challenge Start ------------------*/
	
	public function braintest($sid,$gradeid)
	{
		$query = $this->db->query("select startdate,enddate,level from braintest_mapping where sid='".$sid."' and gradeid='".$gradeid."' and level=1 and status=1");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function isuserplayed_BT($id)
	{
		$query = $this->db->query("select count(gu_id) as total,game_score as score from bt_gamedata where gu_id='".$id."' and BT_LEVEL=1");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function get_btscore($userid)
	{
		$query = $this->db->query("select total_question,attempt_question,answer,gtime,rtime,crtime,game_score from bt_gamedata where gu_id='".$userid."' and BT_LEVEL=1");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getBrainTestLanguage()
	{
		$query = $this->db->query("select id,name from bt_languages where status='Y' order by id asc");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function get_BTGames($sid,$game_grade)
	{
		$query = $this->db->query("SELECT  gid,game_html,img_path from braintest_games where gid=(select gameid from braintest_mapping where gradeid='".$game_grade."' and sid='".$sid."' and level=1)");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function insertone_BT($userid,$cid,$sid,$pid,$gameid,$total_ques,$attempt_ques,$answer,$score,$a6,$a7,$a8,$a9,$lastup_date)
	 {
		 //echo 'hello'; exit;
	//$query = $this->db->query("insert into bt_gamedata (gu_id,gc_id,gs_id,gp_id,g_id,total_question,attempt_question,answer,game_score,gtime,rtime,crtime,wrtime,lastupdate,BT_LEVEL) values('".$userid."','".$cid."','".$sid."','".$pid."','".$gameid."','".$total_ques."','".$attempt_ques."','".$answer."','".$score."','".$a6."','".$a7."','".$a8."','".$a9."','".$lastup_date."','".$this->session->btestlevel."')");
	
	$query = $this->db->query("insert into bt_gamedata (gu_id,gc_id,gs_id,gp_id,g_id,total_question,attempt_question,answer,game_score,gtime,rtime,crtime,wrtime,lastupdate,BT_LEVEL,languageID) values('".$userid."','".$cid."','".$sid."','".$pid."','".$gameid."','".$total_ques."','".$attempt_ques."','".$answer."','".$score."','".$a6."','".$a7."','".$a8."','".$a9."','".$lastup_date."','".$this->session->btestlevel."','".$this->session->currentlanguage."')");
	//echo $this->db->last_query(); exit;
		 
	 }
	 public function getgameid_BT($gamename)
	{
		$query = $this->db->query("select gid from braintest_games where game_html='".$gamename."' ");
	//	echo $this->db->last_query(); exit;
		return $query->result_array();
	}
		 
	/*---------------------------- IAS Challenge End ------------------*/
	
	public function IsBspiTopper($id)
	{
		$query = $this->db->query("select count(userid) as topper,topbspi as value from q1_bspitopper where FIND_IN_SET('".$id."', userid)");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	public function IsCrowniesTopper($id)
	{
		$query = $this->db->query("select count(userid) as topper,topbspi as value from q1_crowniestopper where FIND_IN_SET('".$id."', userid) ");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	public function IsSkillTopper($id)
	{
		$query = $this->db->query("select * from q1_skilltopper where FIND_IN_SET('".$id."', userid) ");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	
	
	public function InsertAccessLog($userid,$ip,$country,$region,$city,$zip,$emailstatus)
	{
		$query = $this->db->query('INSERT INTO user_access_log(userid, ip, login_datetime, country, regionname, city, zip, created_on, mailsend)VALUES("'.$userid.'", "'.$ip.'","'.date("Y-m-d H:i:s").'","'.$country.'","'.$region.'","'.$city.'","'.$zip.'","'.date("Y-m-d H:i:s").'","'.$emailstatus.'")');
	}
	
	public function getSkillWiseAvgScore_opt($userid)		 
	{
		$query = $this->db->query("select id,fname,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =59 and gu_id='".$userid."' group by lastupdate) a1) as skillscorem,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =60 and gu_id='".$userid."' group by lastupdate) a1) as skillscorev,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =61 and gu_id='".$userid."' group by lastupdate) a1) as skillscoref,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =62 and gu_id='".$userid."' group by lastupdate) a1) as skillscorep,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =63 and gu_id='".$userid."' group by lastupdate) a1) as skillscorel

 from users where  id='".$userid."' ");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function myTrophiesAll_opt($userid,$startdate,$enddate)
	{
		$query = $this->db->query("select gu_id AS gu_id,extract(month from lastupdate) AS month,sum(ct) as totstar,id as category from
(select gr.gu_id AS gu_id,gs_id AS id,

(case 
when (round(max(gr.game_score),0) < 20) then 0 
when ((round(max(gr.game_score),0) >= 20) and (round(max(gr.game_score),0) <= 40)) then 1 
when ((round(max(gr.game_score),0) >= 41) and (round(max(gr.game_score),0) <= 60)) then 2 
when ((round(max(gr.game_score),0) >= 61) and (round(max(gr.game_score),0) <= 80)) then 3 
when ((round(max(gr.game_score),0) >= 81) and (round(max(gr.game_score),0) <= 90)) then 4 
when ((round(max(gr.game_score),0) >= 91) and (round(max(gr.game_score),0) <= 100)) then 5 end) AS ct,

gr.lastupdate AS lastupdate from schoolsclp_1819_live.game_reports gr where gs_id IN(59,60,61,62,63) and gu_id='".$userid."'  group by gr.lastupdate,gs_id,gr.gu_id) as a1 where (lastupdate>='".$startdate."' and lastupdate<='".$enddate."')group by month,id ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
						
	}
	
	public function getSkillWiseAvgCLPScore_opt($userid)		 
	{
		$query = $this->db->query("select id,fname,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =59 and gu_id='".$userid."' group by lastupdate) a1) as ME,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =60 and gu_id='".$userid."' group by lastupdate) a1) as VP,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =61 and gu_id='".$userid."' group by lastupdate) a1) as FA,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =62 and gu_id='".$userid."' group by lastupdate) a1) as PS,
 (select (AVG(score))  from (SELECT (AVG(game_score)) as score FROM game_reports WHERE gs_id =63 and gu_id='".$userid."' group by lastupdate) a1) as LI

 from users where  id='".$userid."' ");
		//echo $this->multipledb->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getMyCurrentTrophies_opt($userid)
	{
		$query = $this->db->query("select c.id as catid,c.name,sum(diamond) as diamond,sum(gold) as gold,sum(silver) as silver from 
(select gu_id AS gu_id,id AS catid,(select name from category_skills as cs where cs.id=a1.id) as name,
extract(month from lastupdate) AS month,
floor((sum(ct) / 60)) AS diamond,
floor(((sum(ct) % 60) / 30)) AS gold,
floor((((sum(ct) % 60) % 30) / 15)) AS silver 

from
(select gr.gu_id AS gu_id,gs_id AS id,

(case 
when (round(max(gr.game_score),0) < 20) then 0 
when ((round(max(gr.game_score),0) >= 20) and (round(max(gr.game_score),0) <= 40)) then 1 
when ((round(max(gr.game_score),0) >= 41) and (round(max(gr.game_score),0) <= 60)) then 2 
when ((round(max(gr.game_score),0) >= 61) and (round(max(gr.game_score),0) <= 80)) then 3 
when ((round(max(gr.game_score),0) >= 81) and (round(max(gr.game_score),0) <= 90)) then 4 
when ((round(max(gr.game_score),0) >= 91) and (round(max(gr.game_score),0) <= 100)) then 5 end) AS ct,

gr.lastupdate AS lastupdate from game_reports gr join category_skills cs where gs_id IN(59,60,61,62,63) and ((extract(month from lastupdate) = extract(month from '".date("Y-m-d")."')) and (extract(year from lastupdate) = extract(year from '".date("Y-m-d")."'))) and  gu_id='".$userid."'  group by gr.lastupdate,gs_id) as a1 group by id) a2 right join category_skills as c on c.id=catid  where category_id=1 group by c.id ");
	

		return $query->result_array();
	}
	
		/* ------------------------ Staff FeedBack ------------------------ */
	public function checkValidUser($userid)
	{
		$query = $this->db->query('select * from stafffeedback_list where md5(id)="'.$userid.'" and status=1');
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function getStaffFeedback($userid)
	{
		$query = $this->db->query('select * from stafffeedback where md5(staffid)="'.$userid.'" and status=1');
		return $query->result_array();
	}
	public function StaffFeedbackInsert($staffid,$qus1,$qus2,$qus3,$qus4,$qus5)
	{
		$query = $this->db->query('INSERT INTO stafffeedback( staffid, qus1, qus2, qus3, qus4, qus5, status, created_on) VALUES ("'.$staffid.'","'.$qus1.'","'.$qus2.'","'.$qus3.'","'.$qus4.'","'.$qus5.'",1,"'.date("Y-m-d H:i:s").'")');
		return $lastid=$this->db->insert_id();
	}
	/* ------------------------ Staff FeedBack ------------------------ */
	
	public function getSkGameDetails($userid,$gameid)
	{
		$query = $this->db->query("select (select count(gs_id) from sk_gamedata where gu_id=".$userid." and g_id='".$gameid."' and lastupdate='".date("Y-m-d")."' and gs_id IN(59,60,61,62,63)) as playedgamescount");
		return $query->result_array();
	}
	public function UpdateUserZone($userid,$TimeZone)
	{
		$query = $this->db->query("Update users SET time_zone='".$TimeZone."' where id='".$userid."' "); 
	}
	
	/*----------------- Math Puzzles --------------------------*/

	public function checkMathAssignedToday($userid,$grade_id)
	{
		$query = $this->db->query("SELECT count(id) as assigned from mathrand_selection where user_id='".$userid."' and grade_id='".$grade_id."' and date(created_date)='".date('Y-m-d')."' ");
	
		return $query->result_array();
	}
	public function checkTotalAssignedPuzzles($userid,$grade_id)
	{
		$query = $this->db->query("SELECT count(id) as assigned from mathrand_selection where user_id='".$userid."' and grade_id='".$grade_id."' ");
		return $query->result_array();
	}	
	public function AssignTodayMathPuzzles($userid,$grade_id)
	{
		$query = $this->db->query("SELECT mid FROM math_gamemaster as mg where grade_id = '".$grade_id."' and mid not in(select mid from mathrand_selection where user_id='".$userid."' ) order by mid ASC Limit 1 ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function InertTodayMathPuzzles($gid,$userid,$grade_id)
	{	
		$query = $this->db->query("Insert INTO mathrand_selection(mid,grade_id,user_id,created_date)values('".$gid."','".$grade_id."','".$userid."','".date('Y-m-d h:i:s')."') ");
	}
	public function getTodayPuzzles($userid,$grade_id)
	{
		$query = $this->db->query("SELECT m.mid,mg.gamename,mg.game_html,mg.img_path,(select count(*) as tot_game_played from math_reports where gu_id = '".$userid."' AND lastupdate = '".date('Y-m-d')."') as tot_game_played,(select coalesce(".$this->config->item('starslogic')."(game_score),0)from math_reports where gu_id =  '".$userid."' and lastupdate = '".date('Y-m-d')."') as tot_game_score from mathrand_selection as m join math_gamemaster as mg on mg.mid=m.mid where m.user_id='".$userid."' and mg.grade_id='".$grade_id."' and date(m.created_date)='".date('Y-m-d')."'  ");

		//echo $this->db->last_query(); exit;
		return $query->result_array();

	}
	public function DeletePrevMathCycle($userid,$grade_id)
	{
		$query = $this->db->query("DELETE FROM mathrand_selection where user_id='".$userid."' and grade_id='".$grade_id."' ");
	}

	public function InsertMathData($userid,$cid,$sid,$pid,$gameid,$total_ques,$attempt_ques,$answer,$score,$a6,$a7,$a8,$a9,$lastup_date,$schedule_val)
	{
		$query = $this->db->query("insert into math_reports (gu_id,gc_id,gs_id,gp_id,g_id,total_question,attempt_question,answer,game_score,gtime,rtime,crtime,wrtime,lastupdate,Is_schedule) values('".$userid."','".$cid."','".$sid."','".$pid."','".$gameid."','".$total_ques."','".$attempt_ques."','".$answer."','".$score."','".$a6."','".$a7."','".$a8."','".$a9."','".$lastup_date."','".$schedule_val."')");
		 
	}	 
	public function getMathGameId($gamename)
	{
		$query = $this->db->query("select mid from math_gamemaster where game_html='".$gamename."' ");
		//	echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	
	public function getTotalScoreofMath($userid)
	{
		$query = $this->db->query("select coalesce(Sum(game_score),0) as TotalScore from math_reports where gu_id ='".$userid."' ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function getMathScoreByMonth($userid,$Month,$Year)
	{
		$query = $this->db->query("select lastupdate,Sum(game_score) as DayScore from math_reports where gu_id ='".$userid."' and month(lastupdate)='".$Month."' and year(lastupdate)='".$Year."' group by lastupdate ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function TodayMathPuzzleEligible($userid)
	{
		$query = $this->db->query("select count(DISTINCT gs_id) as tot_games_played from gamedata where gu_id = '".$userid."' AND lastupdate = '".date('Y-m-d')."' ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	public function MathPuzzleEligible($userid)
	{
		$query = $this->db->query("select count(gs_id) as tot_games_played from gamedata where gu_id = '".$userid."' AND lastupdate = '".date('Y-m-d')."' ");
		//echo $this->db->last_query(); exit;
		return $query->result_array();
	}
	/*----------- Math Puzzles ---------------------------*/
	
}
