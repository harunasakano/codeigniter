<?php
/*
編集画面
*/

require_once  APPPATH . 'validation.php';

defined('BASEPATH') OR exit('No direct script access allowed');
class Admin extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model("blog_model");
	}

    public function index(){
			$data['post_id'] = null;
			$data['back'] = null;
			$request_id = null;

			if (isset($_GET['post_id'])){
				$request_id = $_GET['post_id'];
				$data['post_id'] = $request_id;
			}

			//ページ送りに表示するやつ
			$count = $this->blog_model->get_count_all_posts();
			$pagination = ceil($count/5);
			$data['pagination'] = $pagination;

			//記事のリンク渡し
			$this->load->helper('url');
			$data['url'] = base_url();

			//blog_modelのget_all_postメソッドを実行してデータをqueryに格納
			if (isset($_GET['page'])==false){
			$getData = $this->blog_model->get_new_posts();
			//日付をキーにして、タイトルと本文を格納
					for ($i=0; $i <count($getData); $i++) {
						$data['article'][$getData[$i]->created][]= $getData[$i]->id;
						$data['article'][$getData[$i]->created][]= $getData[$i]->title;
					}
						$this->smarty->view('admin.tpl',$data);
			}

			//もし最新以外のページがリクエストされたらこちらを表示
			if (isset($_GET['page'])){
				$page = $_GET['page'];
				$getData_old = $this->blog_model->get_posts($page);
				//日付をキーにして、タイトルと本文を格納
						for ($i=0; $i <count($getData_old); $i++){
							$data['article'][$getData_old[$i]->created][]= $getData_old[$i]->id;
							$data['article'][$getData_old[$i]->created][]= $getData_old[$i]->title;
						}
							$this->smarty->view('admin.tpl',$data);
			}

			//記事選択後、クリックされた後の表示
					if (isset($request_id)){
						$content = $this->blog_model->get_content($request_id);
						$data['single_query_created'] = $content[0]->created;
						$data['single_query_title'] = $content[0]->title;
						$data['single_query_body'] = $content[0]->body;
						$data['single_query_category'] = $content[0]->category_id;
					}
						$this->smarty->view('admin.tpl',$data);

			//変更ボタンが押された時の処理
			$edit = null;
			$edit = $this->input->post('edit');

			if ($edit == 'change') {
				$category = $this->input->post('category');
				$title = $this->input->post('title');
				$content = $this->input->post('content');

				$error_result = entry_validation($category,$title,$content);
			}

			//戻る押下時の処理
					if(isset($_POST['action']) && isset($_GET['page'])){
						$page = $_GET['page'];
						$data['post_id'] = null;
						$request_id = null;
						$data['back'] = $_POST['action'];
						$url = 'http://localhost/codeIgniter/index.php/admin?page='.$page;
						header('Location: '.$url);
						exit();

					}else if(isset($_POST['action'])){
						$data['post_id'] = null;
						$request_id = null;
						$data['back'] = $_POST['action'];
						header('Location:http://localhost/codeIgniter/index.php/admin');
						exit();
					}
	}
}
