<?php

class Auth extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$data["title"] = "Login";

		$this->form_validation->set_rules('user_email', 'E-mail', 'required|trim|valid_email');
		$this->form_validation->set_rules('user_password', 'Password', 'required|trim');
		if ($this->form_validation->run() == FALSE) {
			$this->load->view("auth/v_login", $data);
		} else {
			$this->_loginAction();
		}
	}

	private function _loginAction()
	{
		$email = $this->input->post("user_email");
		$password = $this->input->post("user_password");

		// cek apakah dengan email yang di input ada
		$userData = $this->db->get_where("users", ["user_email" => $email])->row_array();
		if ($userData) {
			// cek apakah password yang dimasukkan benar
			if (password_verify($password, $userData["user_password"])) {
				$data = [
					"user_name" => $userData["user_name"],
					"user_email" => $userData["user_email"],
					"user_phone" => $userData["user_phone"],
					"user_address" => $userData["user_address"],
					"user_avatar" => $userData["user_avatar"],
					"user_role" => $userData["user_role"],
					"created_at" => $userData["created_at"],
					"id_user" => $userData["id_user"],
					"user_password" => $userData["user_password"],

				];
				$tmp = $this->db->select('w1.id as idwil, w1.nama as wilnama, w1.level as willevel')
					->from('users')
					->join('wilayah w1', 'w1.id = users.wilayah')
					->where('users.id_user', $userData['id_user'])->get()->row_array();

				$gudang = $this->db->select('gudang.*, wilayah.nama as wilayah_gudang, wilayah.level as level_wilayah_gudang')
					->from('users')
					->join('admin_gudang', 'admin_gudang.admin = users.id_user')
					->join('gudang', 'gudang.id = admin_gudang.gudang')
					->join('wilayah', 'wilayah.id = gudang.wilayah')
					->get()->result();
					
				$data['gudang'] = [];
				if(!empty($gudang)){
					foreach($gudang as $v)
						$data['gudang'][] = (array) $v;
				}
				if(!empty($tmp))
					$data = array_merge($data, $tmp);

				$this->session->set_userdata('login' . '_' . APPNAME, $data);
				redirect('dashboard');
			} else {
				$this->session->set_flashdata('message', ['message' => 'Password kamu salah', 'type' => 'danger']); 
				redirect("auth");
			}
		} else {
			// $this->session->set_flashdata('message', 'E-mail kamu salah</div>');
			$this->session->set_flashdata('message', ['message' => 'E-mail kamu salah', 'type' => 'danger']); 

			redirect("auth");
		}
	}

	public function logout()
	{
		$this->session->unset_userdata("id_user");
		$this->session->unset_userdata("user_name");
		$this->session->unset_userdata("user_email");
		$this->session->unset_userdata('user_phone');
		$this->session->unset_userdata('user_address');
		$this->session->unset_userdata('user_avatar');
		$this->session->unset_userdata('user_role');
		// $this->session->set_flashdata('message', '<div class="alert alert-success">Kamu berhasil logout</div>');
		$this->session->set_flashdata('message', ['message' => 'Kamu berhasil logout', 'type' => 'success']); 
		
		redirect("auth");
	}

	public function block()
	{
		echo 'Kamu tidak memiliki hak akses';
	}
}
