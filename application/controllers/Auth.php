<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->library('form_validation');
    }
    public function index(){
        if($this->session->userdata('email')){
            redirect('user');
        }
        $this->form_validation->set_rules('email','Email','required|trim|valid_email');
        $this->form_validation->set_rules('password','Password','required|trim');
        if($this->form_validation->run() == false){
            $data["title"] = 'WPU Login Page';
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            //Validasi sukses
            $this->_login();
        }
    }

    private function _login(){
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user',['email'=>$email])->row_array();
        //Cek jika user ada
        if($user){
            //cek jika user active
            if($user['is_active'] == 1){
                //cek password
                if(password_verify($password,$user['password'])){
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if($user['role_id'] == 1){
                        redirect('admin');
                    }else{
                        redirect('user');
                    }        
                } else {
                    $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">
                                                    Wrong password!.
                                                    </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">
                                                    Email is not active!.
                                                    </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">
                                                    Email is not registered!.
                                                    </div>');
            redirect('auth');
        }
    }

    public function registration(){  
        if($this->session->userdata('email')){
            redirect('user');
        }
        $this->form_validation->set_rules('name','Name','required|trim');
        $this->form_validation->set_rules('email','Email','required|trim|valid_email|is_unique[user.email]',[
            'is_unique' => "This email is already exist"
        ]);
        $this->form_validation->set_rules('password1','Password','required|trim|min_length[3]|matches[password2]',[
            'matches' => "Password don't match!",
            'min_length' => 'Password too short!'
        ]);
        $this->form_validation->set_rules('password2','Password','required|trim|matches[password1]');

        if($this->form_validation->run() == false){
            $data["title"] = 'WPU User Registration';
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $email = $this->input->post('email',true);
            $data = [
                'name' => htmlspecialchars($this->input->post('name',true)),
                'email' => htmlspecialchars($email),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'),PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 0,
                'date_created' => time()
            ];      

            //Prepare token
            $token = base64_encode(random_bytes(32));
            $user_token = [
                'email' => $email,
                'token' => $token,
                'date_created' => time()
            ];
            
            $this->db->insert('user',$data);
            $this->db->insert('user_token',$user_token);

            $this->_sendEmail($token, 'verify');

            $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
                                                    Registration Complete. Please activate your account!
                                                    </div>');
            redirect('auth');
        } 
        
    }

    private function _sendEmail($token, $type){
        //Config for Email class in codeigniter
        $config = [
            'protocol'  => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_user' => 'cahyadi.nfw123@gmail.com',
            'smtp_pass' => 'Bayu2702',
            'smtp_port' => 465,
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'newline'   => "\r\n"
        ];

        $this->load->library('email',$config);
        $this->email->initialize($config);

        $this->email->from('cahyadi.nfw123@gmail.com', 'Admin Dashboard');
        $this->email->to($this->input->post('email'));
        if($type == 'verify'){
            $this->email->subject('Account Verification');
            $this->email->message('Click this link to activate your account : <a href="'.base_url(). 'auth/verify?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Activate</a>');
        } else if ($type == 'forgot'){
            $this->email->subject('Reset Password');
            $this->email->message('Click this link to reset your password : <a href="'.base_url(). 'auth/resetpassword?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Reset</a>');
        }
        

        if ($this->email->send()){
            return true;
        } else {
            echo $this->email->print_debugger();
            die;
        }
    }

    public function verify(){
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        if($user){
            $user_token =$this->db->get_where('user_token', ['token' => $token])->row_array();
            if($user_token){
                if(time() - $user_token['date_created'] < (60*60*24)){
                    // if all condition is true: email in url, token in url, token is still valid
                    $this->db->set('is_active',1);
                    $this->db->where('email',$email);
                    $this->db->update('user');
                    //delete token
                    $this->db->delete('user_token',['email' => $email]);
                    $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">'.$email.' has been activated. Please Login!</div>');
                    redirect('auth');
                } else {
                    // if token expired

                    $this->db->delete('user',['email' => $email]);
                    $this->db->delete('user_token',['email' => $email]);

                    $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">
                                                    Account Activation Failed! Token Expired!
                                                    </div>');
                    redirect('auth');
                }
            } else {
                //wrong token in url
                $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">
                                                    Account Activation Failed! Token Invalid!
                                                    </div>');
            redirect('auth');
            }
        } else {
            // Wrong email in url
            $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">
                                                    Account Activation Failed! Wrong Email!
                                                    </div>');
            redirect('auth');
        }
    }

    public function logout(){
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
                                                    You have been logout!
                                                    </div>');
        redirect('auth');
    }

    public function blocked(){
        $data['title'] = 'Access Blocked';
        $data['user'] = $this->db->get_where('user',['email' => $this->session->userdata('email')])->row_array();
        
        
        $this->load->view('templates/header',$data);
        $this->load->view('auth/blocked',$data);
        $this->load->view('templates/footer');
    }

    public function forgotPassword(){
        $data["title"] = 'Forgot Password';

        $this->form_validation->set_rules('email','Email','required|trim|valid_email');
        if($this->form_validation->run() == false){
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/forgot-password');
            $this->load->view('templates/auth_footer');
        } else {
            $email = $this->input->post('email');
            $user = $this->db->get_where('user', [
                'email' => $email,
                'is_active' => 1
            ])->row_array();

            if($user){
                $token = base64_encode(random_bytes(32));
                $user_token = [
                    'email' => $email,
                    'token' => $token,
                    'date_created' => time()
                ];

                $this->db->insert('user_token',$user_token);
                $this->_sendEmail($token, 'forgot');

                $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
                                                    Please check your email to reset your password!
                                                    </div>');
                redirect('auth/forgotpassword');
            } else {
                $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">
                                                    Email is not registered or activated!
                                                    </div>');
                redirect('auth/forgotpassword');
            }
        }
    }

    public function resetPassword(){
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        if($user){
            $user_token =$this->db->get_where('user_token', ['token' => $token])->row_array();
            if($user_token){
                $this->session->set_flashdata('reset_email', $email);
                $this->changePassword();
            }else {
                // Wrong token in url
                $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">
                Reset Password Failed! Wrong Token!
                </div>');
                redirect('auth');
            }
        } else {
            // Wrong email in url
            $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">
                                                    Reset Password Failed! Wrong Email!
                                                    </div>');
            redirect('auth');
        }
    }

    public function changePassword(){

        if(!$this->session->userdata('reset_email')){
            redirect('auth');
        }
        $data["title"] = 'Change Password';

        $this->form_validation->set_rules('password1','New Password','required|trim|min_length[3]|matches[password2]',[
            'matches' => "Password don't match!",
            'min_length' => 'Password too short!'
        ]);
        $this->form_validation->set_rules('password2','Confirm New Password','required|trim|matches[password1]');
        
        
        if($this->form_validation->run() == false){
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/change-password');
            $this->load->view('templates/auth_footer');
        } else {
            $password = password_hash($this->input->post('password1'),PASSWORD_DEFAULT);
            $email = $this->session->userdata('reset_email');

            $this->db->set('password',$password);
            $this->db->where('email',$email);
            $this->db->update('user');

            $this->session->unset_userdata('reset_email');
            //delete token
            $this->db->delete('user_token',['email' => $email]);

            $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
                                                    Password has been changed! Please Login
                                                    </div>');
            redirect('auth');

        }
    } 
}