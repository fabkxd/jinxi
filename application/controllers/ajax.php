<?php
class Ajax extends CI_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->model ( 'user_model' );
		$this->load->model ( 'love_model' );
		$this->load->model ( 'post_model' );
		$this->load->library ( 'session' );
		$this->load->helper ( 'url' );
	}
	public function login($mail1, $mail2, $pw) {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$logresult = $this->user_model->login ( $mail1 . '@' . $mail2, $pw );
		if ($logresult == 1) {
			$user = $this->user_model->get_user2 ( $mail1 . '@' . $mail2 );
			$this->session->set_userdata ( 'login_user', $user ['user_id'] );
		}
		echo $logresult;
	}
	public function logout() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$this->session->unset_userdata ( 'login_user' );
	}
	public function regidit($mail1, $mail2, $pw) {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$uname = $_POST ['user_name'];
		if (strlen ( $mail1 ) == 0 || strlen ( $mail2 ) == 0 || strlen ( $uname ) == 0 || strlen ( $pw ) < 6)
			exit ( "PERMISSION DENIED!" );
		$regres = $this->user_model->regidit ( $mail1 . '@' . $mail2, $uname, $pw );
		if ($regres == '0')
			exit ( "0" );
		$this->load->library ( 'encrypt' );
		$encrypted_string_mail = $this->encrypt->encode ( explode ( '*&*', $regres )[0] );
		$encrypted_string_code = $this->encrypt->encode ( explode ( '*&*', $regres )[1] );
		
		$this->load->library ( 'email' );
		// 设置Email参数
		$config ['protocol'] = 'smtp';
		$config ['smtp_host'] = 'smtp.163.com';
		$config ['smtp_user'] = 'jinxicn2013';
		$config ['smtp_pass'] = 'jinxicn';
		$config ['smtp_port'] = '25';
		$config ['charset'] = 'utf-8';
		$config ['wordwrap'] = TRUE;
		$config ['mailtype'] = 'html';
		$this->email->initialize ( $config );
		// 发送
		$content = '
		<style type="text/css">
a.footer{
  	color: #FFFFFF;
}
a.footer:hover {
 	color: #CCCCCC;
}
p.footer{
	color: #FFFFFF;
	font-size: 14px;
	font-family: 微软雅黑,黑体,幼圆,宋体;
	margin-top: 5px;
}

       </style>
	<div style="width: 450px; margin-top: 15px; margin-left: auto; margin-right: auto;"><img 

style="width: 100%;" src="http://xn--wmqr18c.cn/img/icon/invite.png" /></div>
	<p style="font-size: 22px; font-family: 微软雅黑,黑体,宋体">&nbsp;&nbsp;&nbsp;&nbsp;尊敬的用户<span style="color: #1ABC9C"> ' . $uname . ' </span>您好，欢迎加入今昔网，您的账号已经注册完毕，请点击以下链接完成验证：<a href="http://xn--wmqr18c.cn/account/verify?mail=' . $encrypted_string_mail . '&code=' . $encrypted_string_code . '">立即激活</a></p>
	<div class="row"
		style="height: 35px; background-color: #1ABC9C; text-align: center; margin-bottom: 

10px; ">
		<p class="footer">
			<a href="#" class="footer">关于今昔</a><span> &nbsp;&nbsp;| &nbsp;&nbsp;</span>
			<a href="#" class="footer">今昔历程</a><span> &nbsp;&nbsp;| &nbsp;&nbsp;</span>
			<a href="#" class="footer">联系我们</a><span> &nbsp;&nbsp;| &nbsp;&nbsp;</span>
			<a href="#" class="footer">用户协议</a><span> &nbsp;&nbsp;| &nbsp;&nbsp;</span>
			<a href="#" class="footer">帮助中心</a><span> &nbsp;&nbsp;| &nbsp;&nbsp;</span>
			<a href="#" class="footer">意见建议</a>
		</p>
	</div>

	<div style="text-align: center; margin-bottom: 40px; margin-top: 10px;">
		<p class="footerinfo">&copy; 2013 今昔网 &middot;
			版权所有&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 京ICP备13053152号</p>
		<p class="footerinfo">后夏科技&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;地址：北京市海淀区</p>
		<p class="footerinfo">Designed and Developed by FABKXD</p>
	</div>
				';
		$this->email->from ( 'jinxicn2013@163.com', '今昔网' );
		$this->email->to ( $mail1 . '@' . $mail2 );
		$this->email->subject ( '今昔网账号邮件验证' );
		$this->email->message ( $content );
		
		$this->email->send ();
		echo 1;
	}
	public function captcha() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$this->load->helper ( 'url' );
		$this->load->helper ( 'captcha' );
		$vals = array (
				'word' => rand ( 1000, 10000 ),
				'img_path' => './img/captcha/',
				'img_url' => base_url () . 'img/captcha/',
				'img_width' => '121',
				'img_height' => '45',
				'expiration' => 30 
		);
		
		$cap = create_captcha ( $vals );
		$img = $cap ['image'];
		$time = $cap ['time'];
		$num = $cap ['word'];
		$this->session->set_userdata ( 'captcha', $num );
		echo $img;
	}
	public function check_mail($mail1, $mail2) {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		echo $this->user_model->check_mail ( $mail1 . '@' . $mail2 );
	}
	public function check_captcha() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$captcha = $_POST ['captcha'];
		if ($this->session->userdata ( 'captcha' ) == $captcha) {
			echo 1;
		} else {
			echo 0;
		}
	}
	public function upload_photo($uid) { // ajax上传图片
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$randpwd = '_';
		for($i = 0; $i < 9; $i ++) {
			$tp = mt_rand ( 97, 122 );
			$randpwd .= chr ( $tp );
		}
		$config ['upload_path'] = './img/head/';
		$config ['allowed_types'] = 'gif|jpg|png';
		$config ['max_size'] = '2048';
		$config ['file_name'] = $uid . $randpwd;
		$config ['overwrite'] = true;
		$this->load->library ( 'upload', $config ); // 加载upload类
		
		if (! $this->upload->do_upload ()) { // 一定要写表单对应字段
			$data = $this->upload->display_errors ( '', '' );
			echo $data;
		} else {
			$data = $this->upload->data ()['file_name'] . '***' . $this->upload->data ()['image_width'] . '***' . $this->upload->data ()['image_height'];
			echo $data; // 成功后返回相对路径+图片名
		}
	}
	public function save_photo($uid) { // 生产裁剪后图片
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$config ['image_library'] = 'gd2';
		$config ['source_image'] = './img/head/' . $uid . '_' . $_POST ['hitype'];
		$config ['new_image'] = './img/head/' . $uid . '_' . $_POST ['hitype'];
		$config ['maintain_ratio'] = FALSE; // 保证设置的长宽有效
		$config ['x_axis'] = $_POST ['p_x'] * $_POST ['p_k']; // 一定要乘以p_k，因为这里存放的
		$config ['y_axis'] = $_POST ['p_y'] * $_POST ['p_k']; // 是原图而不是浏览器上经过缩放的
		$config ['width'] = $_POST ['p_w'] * $_POST ['p_k']; // 的图
		$config ['height'] = $_POST ['p_h'] * $_POST ['p_k'];
		$this->load->library ( 'image_lib', $config );
		if (! $this->image_lib->crop ()) {
			echo $this->image_lib->display_errors ();
		} else {
			$res = $this->user_model->changehi ( $uid, $_POST ['hitype'] );
			if ($res > 0)
				echo 'success';
		}
	}
	public function picture($pid, $num) { // ajax上传图片
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$picname = $pid . '_' . $num;
		$config ['upload_path'] = './img/post/';
		$config ['allowed_types'] = 'jpg';
		$config ['max_size'] = '2048';
		$config ['file_name'] = $picname;
		$config ['overwrite'] = true;
		$this->load->library ( 'upload', $config ); // 加载upload类
		
		if (! $this->upload->do_upload ( 'image' . $num )) { // 一定要写表单对应字段
			$data = $this->upload->display_errors ( '', '' );
			echo $data;
		} else {
			$imgsrc = $this->upload->data ()['file_name'];
			$imgw = $this->upload->data ()['image_width'];
			$imgh = $this->upload->data ()['image_height'];
			if ($imgw > 800) {
				$config ['image_library'] = 'gd2';
				$config ['source_image'] = './img/post/' . $imgsrc;
				$config ['new_image'] = './img/post/' . $imgsrc;
				$config ['maintain_ratio'] = TRUE; // 保证设置的长宽有效
				$config ['width'] = 800; // 的图
				$config ['height'] = 800 * $imgh / $imgw;
				$this->load->library ( 'image_lib', $config );
				$this->image_lib->resize ();
			}
			
			$data = 'success!';
			echo $data;
		}
	}
	public function delete_file($url) {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		unlink ( './img/head/' . $url );
	}
	public function savebi() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$username = $_POST ['username'];
		$sex = $_POST ['sex'];
		$school = $_POST ['school'];
		$degree = $_POST ['degree'];
		$year = $_POST ['year'];
		$sign = $_POST ['sign'];
		$qq = $_POST ['qq'];
		$phone = $_POST ['phone'];
		$user_id = $this->session->userdata ( 'login_user' );
		$this->user_model->savebi ( $user_id, $username, $sex, $school, $degree, $year, $sign, $qq, $phone );
	}
	public function saveac() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$mail_on = $_POST ['mailcheck'];
		$qq_on = $_POST ['qqcheck'];
		$phone_on = $_POST ['phonecheck'];
		$sign_on1 = $_POST ['sign1'];
		$sign_on2 = $_POST ['sign2'];
		$right_on = $_POST ['righton'];
		$user_id = $this->session->userdata ( 'login_user' );
		$this->user_model->saveac ( $user_id, $mail_on, $qq_on, $phone_on, $sign_on1, $sign_on2, $right_on );
	}
	public function savest() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$namecolor = $_POST ['namecolor'];
		$auto_on = $_POST ['autoon'];
		$user_id = $this->session->userdata ( 'login_user' );
		$this->user_model->savest ( $user_id, $namecolor, $auto_on );
	}
	public function mem_url() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$memurl = $_POST ['memurl'];
		$this->session->set_userdata ( 'mem_url', $memurl );
	}
	public function get_mem_url() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		echo $this->session->userdata ( 'mem_url' );
	}
	public function addlove() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$lover = $_POST ['lover'];
		$lovee = $_POST ['lovee'];
		$love = $_POST ['love'];
		$this->love_model->add_love ( $lover, $lovee, $love );
	}
	public function deletelove() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$lover = $_POST ['lover'];
		$lovee = $_POST ['lovee'];
		$love = $_POST ['love'];
		$this->love_model->delete_love ( $lover, $lovee, $love );
	}
	public function addfocus() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$focuser = $_POST ['focuser'];
		$focusee = $_POST ['focusee'];
		$focus = $_POST ['focuss'];
		$this->post_model->add_focus ( $focuser, $focusee, $focus );
	}
	public function deletefocus() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$focuser = $_POST ['focuser'];
		$focusee = $_POST ['focusee'];
		$focus = $_POST ['focuss'];
		$this->post_model->delete_focus ( $focuser, $focusee, $focus );
	}
	public function addcomment() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$user_id = $_POST ['user_id'];
		$subject_id = $_POST ['subject_id'];
		$ctype = $_POST ['ctype'];
		$cscore = $_POST ['cscore'];
		$ccontent = $_POST ['ccontent'];
		$this->love_model->add_comment ( $user_id, $subject_id, $ctype, $cscore, $ccontent );
	}
	public function addreport() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$object_id = $_POST ['object_id'];
		$rtype = $_POST ['rtype'];
		$rcontent = $_POST ['rcontent'];
		$this->love_model->add_report ( $object_id, $rtype, $rcontent );
	}
	public function show_user_page() {
		if ($_SERVER ['REQUEST_METHOD'] == 'GET')
			exit ( "PERMISSION DENIED!" );
		$tab = $_POST ['tab'];
		$page = $_POST ['page'];
		$data ['cur_user'] = $this->user_model->get_user ( $_POST ['uid'] );
		$data ['baseurl'] = base_url ();
		switch ($tab) {
			case 0 :
				{
					$this->load->view ( 'user/thisrec', $data );
					break;
				}
			case 1 :
				{
					$this->load->view ( 'user/bbsrec', $data );
					break;
				}
			case 2 :
				{
					$res = $this->user_model->get_mys ( $_POST ['uid'], $page );
					$data ['total'] = $res ['total'];
					$data ['page'] = $page;
					$data ['mys'] = $res ['data'];
					$this->load->view ( 'user/mypost', $data );
					break;
				}
			case 3 :
				{
					$this->load->view ( 'user/myfocus', $data );
					break;
				}
			case 4 :
				{
					$res = $this->user_model->get_loves ( $_POST ['uid'], $page );
					$data ['total'] = $res ['total'];
					$data ['page'] = $page;
					$data ['loves'] = $res ['data'];
					$this->load->view ( 'user/mylove', $data );
					break;
				}
			case 5 :
				{
					$res = $this->user_model->get_comms ( $_POST ['uid'], $page, 5 );
					$data ['total'] = $res ['total'];
					$data ['page'] = $page;
					$data ['ctype'] = 5;
					$data ['cur_id'] = $_POST ['uid'];
					$data ['comms'] = $res ['data'];
					$this->load->view ( 'user/mycomm', $data );
					break;
				}
			case 6 :
				{
					$res = $this->user_model->get_comms ( $_POST ['uid'], $page, 6 );
					$data ['total'] = $res ['total'];
					$data ['page'] = $page;
					$data ['ctype'] = 6;
					$data ['cur_id'] = $_POST ['uid'];
					$data ['comms'] = $res ['data'];
					$this->load->view ( 'user/mycomm', $data );
					break;
				}
			case 7 :
				{
					$res = $this->user_model->get_comms ( $_POST ['uid'], $page, 7 );
					$data ['total'] = $res ['total'];
					$data ['page'] = $page;
					$data ['ctype'] = 7;
					$data ['cur_id'] = $_POST ['uid'];
					$data ['comms'] = $res ['data'];
					$this->load->view ( 'user/mycomm', $data );
					break;
				}
			case 8 :
				{
					$this->load->view ( 'user/mymess', $data );
					break;
				}
			default :
				break;
		}
	}
}