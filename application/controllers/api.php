<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {
	function __construct(){
		parent::__construct();

		date_default_timezone_set('Asia/Jakarta');
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		
	}

	private function check_sesi(){
		$token = $this->input->post('f_token');
		$device = $this->input->post('f_device');

		//$token = 'a6bccee9979eef5c66532b5a36880d39';
		//$device = 'ffffffff-be46-4574-ffff-ffffbdceeae1';
		
		if($token || $device){
			$sql = "SELECT * FROM sesi WHERE 
				sesi_key = ? AND sesi_device = ? 
				AND sesi_status = ?";
			// $this->db->where('sesi_key', $token);
			// $this->db->where('sesi_status', 1);
			// $this->db->where('sesi_device', $device);
			$query = $this->db->query($sql, array($token, $device, 1));
			if($query->num_rows() > 0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}

		
		
	}

	public function login(){ 
		$data = array();
		$device = $this->input->post('device');
		$email =  $this->input->post("t_email");
		$password =  $this->input->post("t_password");
		$device_type = $this->input->post("device_type");

		if($email == '' || $password == ''){
			$data['result'] = 'false';
			$data['msg'] = 'Silahkan isi email dan  password anda.';
			echo json_encode($data);
			return;
			
		}
		
		$this->db->where('email', $email);
		$this->db->where('password', md5($password));
		
		$query = $this->db->get('tb_user');
		if($query->num_rows() > 0){
			$q = $query->row();

			//delete semua sesi user ini sebelumnya
			$this->db->where('id_user' , $q->id_user);
			$this->db->update('tb_sesi', array('sesi_status' => 9));					
			//create token
			$key = md5(date('Y-m-d H:i:s').$device);
			//masukkan kedlam tabel sesi
			$simpan = array();
			$simpan['sesi_key'] =  $key;
			$simpan['id_user'] = $q->id_user;
			$simpan['sesi_device'] = $device;
			$status = $this->db->insert('tb_sesi', $simpan);
			if($status){
				$data['result'] = 'true';
				$data['token'] =  $key;
				$data['data'] = $q;
				$data['msg'] = 'Login berhasil.';
				$data['idUser'] = $q->id_user;

				if(!empty($device_type)){
					if($device_type == "ios"){
						$token = $this->input->post("token");
						$data['player_id'] = $this->register_player_id($token, $q->id_user);
					}
				}
			}else{
				$data['result'] = 'false';
				$data['token'] = '';
				$data['idUser'] = '';
				$data['msg'] = 'Error create sesi login, Silahkan coba lagi.';
			}
		}else{			
			$data['result'] = 'false';
			$data['msg'] = 'Username atau password salah.';
			
		}		
		echo json_encode($data);
	}


	

	public function daftar(){ 
		$data = array();
		$usernama = $this->input->post('usernama');
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$hp = $this->input->post('phone');
		$alamat = $this->input->post('alamat');
		
		//check email in di database
		$this->db->where('email', $email);
		
		$q = $this->db->get('tb_user');

		if($q->num_rows() > 0) {
			$data['result'] = 'false';
			$data['msg'] = 'Email anda sudah terdaftar, silahkan untuk login.';
		}else{		
			$simpan = array();
			
			$simpan['password'] = md5($password);
			$simpan['usernama'] = $usernama;
			$simpan['email'] = $email;
			$simpan['alamat'] = $alamat;
			
			$simpan['no_hp'] = $hp;
			

			$status = $this->db->insert('tb_user',$simpan);
			
			if($status){				
				$data['result'] = 'true';
				$idUser = $this->db->insert_id();		
				
				$data['msg'] = 'Pendaftaran berhasil';
				


				
			}else{
				$data['result'] = 'false';
				$data['msg'] = 'Pendafatran gagal, silahkan coba kembali';
			}

		}
		
		#pre($this->db->last_query());
		echo json_encode($data);
	}

	public function get_menu(){ 
		$data = array();
	
		$sql = "SELECT * FROM tb_menu ORDER BY id_menu DESC";
        
		$q = $this->db->query($sql);
		if($q->num_rows() > 0){				
			$data['result'] = 'true';
			$data['msg'] = 'Data semua menu';
			$data['data'] = $q->result();
		}else{
			$data['result'] = 'false';
			$data['msg'] = 'Tidak ada data menu';
		}
		
		//#pre($this->db->last_query());
		echo json_encode($data);
	}

	public function get_menuByID(){ 
		$data = array();

		$id_menu = $this->input->post('id_menu');
	
		$sql = "SELECT * FROM tb_info WHERE id_menu = '$id_menu' ORDER by id_info DESC";
        
		$q = $this->db->query($sql);
		if($q->num_rows() > 0){				
			$data['result'] = 'true';
			$data['msg'] = 'Data semua menu';
			$data['data'] = $q->result();
		}else{
			$data['result'] = 'false';
			$data['msg'] = 'Tidak ada data menu';
		}
		
		//#pre($this->db->last_query());
		echo json_encode($data);
	}

	public function get_infoByID(){ 
		$data = array();

		$id_info = $this->input->post('id_info');
	
		$sql = "SELECT * FROM tb_info WHERE id_info = '$id_info' ORDER by id_info DESC";
        
		$q = $this->db->query($sql);
		if($q->num_rows() > 0){				
			$data['result'] = 'true';
			$data['msg'] = 'Data detail info';
			$data['data'] = $q->result();
		}else{
			$data['result'] = 'false';
			$data['msg'] = 'Tidak ada data info';
		}
		
		//#pre($this->db->last_query());
		echo json_encode($data);
	}

	public function get_allMenu(){ 
		$data = array();

		// $id_info = $this->input->post('id_info');
	
		$sql = "SELECT * FROM tb_info  where id_menu = '8' ORDER by id_info DESC";
        
		$q = $this->db->query($sql);
		if($q->num_rows() > 0){				
			$data['result'] = 'true';
			$data['msg'] = 'Data  info map';
			$data['data'] = $q->result();
		}else{
			$data['result'] = 'false';
			$data['msg'] = 'Tidak ada data info';
		}
		
		//#pre($this->db->last_query());
		echo json_encode($data);
	}



	public function get_Slider(){ 
		$data = array();
	
		$sql = "SELECT * FROM tb_info ORDER BY id_info DESC";
        
		$q = $this->db->query($sql);
		if($q->num_rows() > 0){				
			$data['result'] = 'true';
			$data['msg'] = 'Data semua Slider';
			$data['data'] = $q->result();
		}else{
			$data['result'] = 'false';
			$data['msg'] = 'Tidak ada data Slider';
		}
		
		//#pre($this->db->last_query());
		echo json_encode($data);
	}

	


	
	



	
}
	


/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */