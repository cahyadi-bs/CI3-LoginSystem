<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('Menu_Model','menu');
        is_logged_in(); // fungsi Helper = cek sudah login dan cek rolenya
    }

    public function index(){
        $data['title'] = 'Menu Management';
        $data['user'] = $this->db->get_where('user',['email' => $this->session->userdata('email')])->row_array();

        $data['menu'] = $this->db->get('user_menu')->result_array();

        $this->form_validation->set_rules('menu','Menu','required');
        if($this->form_validation->run()==false){
            $this->load->view('templates/header',$data);
            $this->load->view('templates/sidebar',$data);
            $this->load->view('templates/topbar',$data);
            $this->load->view('menu/index',$data);
            $this->load->view('templates/footer');
        } else {
            $this->db->insert('user_menu',['menu' => $this->input->post('menu')]);
            $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
                                                    New Menu Added!.
                                                    </div>');
            redirect('menu');
        }
        
    }

    public function deletemenu($id){
        $this->menu->deleteMenu($id);
        $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
                                                    Menu Deleted!.
                                                    </div>');
        redirect('menu');
    }

    public function deletesubmenu($id){
        $this->menu->deleteSubMenu($id);
        $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
                                                    Sub Menu Deleted!.
                                                    </div>');
        redirect('menu/submenu');
    }


    public function submenu(){
        $data['title'] = 'Submenu Management';
        $data['user'] = $this->db->get_where('user',['email' => $this->session->userdata('email')])->row_array();

        $data['subMenu'] = $this->menu->getSubMenu();
        $data['menu'] = $this->db->get('user_menu')->result_array();
        
        $this->form_validation->set_rules('title','Title','required');
        $this->form_validation->set_rules('menu_id','Menu','required');
        $this->form_validation->set_rules('url','URL','required');
        $this->form_validation->set_rules('icon','Icon','required');

        if($this->form_validation->run()==false){
        $this->load->view('templates/header',$data);
        $this->load->view('templates/sidebar',$data);
        $this->load->view('templates/topbar',$data);
        $this->load->view('menu/submenu',$data);
        $this->load->view('templates/footer');
        } else {
            $data = [
                'menu_id' => $this->input->post('menu_id'),
                'title' => $this->input->post('title'),
                'url' => $this->input->post('url'),
                'icon' => $this->input->post('icon'),
                'is_active' => $this->input->post('is_active')
            ];
            $this->db->insert('user_sub_menu',$data);
            $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
                                                    New Sub Menu Added!.
                                                    </div>');
            redirect('menu/submenu');
        }
    }

}