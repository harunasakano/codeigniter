<?php
class Login extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model("User_model");

	}

	public function index(){

		//ログインチェック
		if(isset($_SESSION['status'])){
		 	$data['login_user'] = $_SESSION['status'];
		 }

		$this->smarty->view('login.tpl');

		//postがあった時の処理
		if(isset($_POST['mode'])){
			//ユーザidとパスを変数に格納し、エスケープ処理
			$user_id = $_POST['user_id'];
			$password = $_POST['password'];
			$user_id = htmlspecialchars($user_id, ENT_QUOTES, "UTF-8");
			$password = htmlspecialchars($password, ENT_QUOTES, "UTF-8");
		}

		//ユーザID及びパスワード新規登録リクエストされた時の処理
		if(isset($_POST['registration'])){

		//エスケープ処理したIDとパスをバリデーションチェックにかける
		$check_result = null;
		$check_User_result = null;

		$check_result = $this->validation($user_id,$password);
		$check_User_result = $this->User_model->check_duplication_userid($user_id);

			if(isset($check_result) || $check_User_result>=1){
				//バリデーションにエラーがある場合
				if(isset($check_result)){
					$data['error_text'] = $check_result;

				//データベースから同一user_id検索し帰ってきた場合
				}else if ($check_User_result>=1){
					$data['user_duplication'] = "そのユーザIDは既に使用されています";
				}
				$this->smarty->view('login.tpl',$data);

			//バリデーションOK、同一ユーザもいない場合新規登録
			}else{
				$this->User_model->save_new_user($user_id,$password);
				header('Location:http://localhost/codeIgniter/index.php/login');
				exit();
			}

		//ログインリクエストされた場合の処理
		}else if(isset($_POST['login'])){
			$login_messege = null;
			//データベースから一致するユーザIDとパスワードを検索して
			$login_result = null;
			$login_result = $this->User_model->user_matched($user_id,$password);

			//一致したデータが返ってくればログイン成功
			if (empty($login_result)==false) {
				$login_result['status'] = $login_result[0]->id;
				$this->session->set_userdata($login_result);

				$success_message =  "login";
				$jump_url = "http://localhost/codeIgniter/index.php/blog?success=".$success_message;
				header("Location: ".$jump_url);
				exit();

			}else{
				$login_false_message =  "ユーザ名、またはパスワードに誤りがあります";
				$data['login_result'] = $login_false_message;
				$this->smarty->view('login.tpl',$data);
			}
	}
	}
	//ユーザIDが半角英数字10文字満たし
	//パスワードが半角英数字8文字満たしてたら
	function validation($user_id,$password){
		$error = null;
		//未入力チェック
		if(empty($user_id) && empty($password)){
			$error[] = "入力欄が空欄のままです";
		}else if (empty($user_id)) {
			$error[] = "ユーザIDが入力されていません";
		}else if(empty($password)){
			$error[] = "パスワードが入力されていません";
		}

		//半角英数チェック
		if (preg_match("/^[a-zA-Z0-9]+$/", $user_id)==false && preg_match("/^[a-zA-Z0-9]+$/", $password)==false) {
    		$error[] = "ユーザID・パスワードに指定できるのは半角英数字のみです";
		}else if(preg_match("/^[a-zA-Z0-9]+$/", $user_id)==false){
			$error[] = "ユーザIDに指定できるのは半角英数字のみです";
		}else if(preg_match("/^[a-zA-Z0-9]+$/", $password)==false){
			$error[] = "パスワードに指定できるのは半角英数字のみです";
		}

		//文字数チェック
		if (strlen($user_id)>=10 && strlen($password)>=8){
			$error[] = "ユーザIDは10文字、パスワードは8文字まででご指定下さい";
		}else if(strlen($user_id)>=10){
			$error[] = "ユーザIDは10文字までです";
		}else if(strlen($password)>=8){
			$error[] = "パスワードは8文字までです";
		}
		return $error;
	}
}
