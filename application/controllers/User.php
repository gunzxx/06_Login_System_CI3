<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    private $data = [];

    public function __construct()
    {
        parent::__construct();

        is_login();

        $this->load->model("User_model","userModel");

        $this->data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    }

    public function index()
    {
        redirect('user/profile');
    }
    
    public function profile()
    {
        $data['user'] = $this->userModel->getUser();
        $data['active'] = 'profile';
        $this->template('user/profile',$data);
    }
    
    public function edit()
    {
        $data = $this->data;
        $data['active'] = 'edit profile';

        $oldimage = $data['user']['image'];

        $this->form_validation->set_rules('nickname',"Nickname",'required|trim');
        
        if($this->form_validation->run()==false){
            $this->template('user/edit',$data);
        }
        else{
            $nickname = $this->input->post('nickname');
            $email = $this->input->post('email');
            $image = $_FILES['image']['name'];

            if($image){
                if($image!=$oldimage){
                    $config['allowed_types'] = 'gif|jpg|png|svg';
                    $config['max_size']     = '100';
                    $config['upload_path'] = './assets/img/profile/';
    
                    $this->load->library('upload', $config);
                    
                    if ($this->upload->do_upload('image')){
                        if($oldimage != "default.svg"){
                            unlink(FCPATH.'assets/img/profile/'.$oldimage);
                        }
    
                        $newimage = $this->upload->data('file_name');
                        $this->db->set('image',$newimage);
                    }
                    else {
                        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . $this->upload->display_errors() . '</div>');
                    }
                }
            }

            $this->db->update('user',['nickname'=>$nickname], ['email' => $email]);

            $this->session->set_flashdata('message', 'Data has been updated!');
            redirect('user/edit');
        }
    }

    public function password()
    {
        $data = $this->data;
        $savePassword = $data['user']['password'];
        $user_id = $data['user']['id'];
        $data['active'] = 'change password';

        // dd($savePassword);
        // dd(password_needs_rehash($savePassword,PASSWORD_DEFAULT));
        
        $this->form_validation->set_rules('oldpassword', 'Old Password', 'trim');
        $this->form_validation->set_rules('password1', 'New Password', 'required|trim|min_length[3]|matches[password2]');
        $this->form_validation->set_rules('password2', 'Repeat Password', 'required|trim|min_length[3]|matches[password1]');

        if ($this->form_validation->run() == false) {
            $this->template('user/password', $data);
        }
        else {
            $oldPassword = $this->input->post('oldpassword');
            $password = $this->input->post('password1');

            if(!(password_verify($oldPassword, $savePassword))){
                $this->session->set_flashdata('message','Wrong current password!');
                return redirect("user/password");
            }
            else{
                if(password_verify($password, $savePassword)) {
                    $this->session->set_flashdata('message',"New password can't be assign!");
                    return redirect("user/password");
                }
                else{
                    $passwordHash = password_hash($password,PASSWORD_DEFAULT);
                    $this->db->update('user',['password'=>$passwordHash],['id'=>$user_id]);
                    
                    $this->session->set_flashdata('message', "Password has been update!");
                    return redirect("user/password");
                }
            }
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        $this->session->set_flashdata('error', 'You has been logout!');
        redirect('auth/login');
    }
}
