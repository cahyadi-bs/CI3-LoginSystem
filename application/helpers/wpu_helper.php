<?php 

function is_logged_in(){
    $ci = get_instance(); //  mengambil library yang ada di ci, tanpa ini $this tidak mengenali
    // $this diganti
    if(!$ci->session->userdata('email')){
        redirect('auth');
    } else {
        $role_id = $ci->session->userdata('role_id');
        $menu = $ci->uri->segment(1);

        // SELECT * FROM user_menu WHERE menu = $menu; 
        // Query untuk mengambil id menu
        $queryMenu = $ci->db->get_where('user_menu',['menu' => $menu])->row_array();
        $menu_id = $queryMenu['id'];

        // SELECT * FROM user_access_menu WHERE role_id = $role_id AND 
        $userAccess = $ci->db->get_where('user_access_menu',[
            'role_id' => $role_id,
            'menu_id' => $menu_id
        ]);

        if($userAccess->num_rows() < 1){
            redirect('auth/blocked');
        }
    }
}

function check_access($role_id,$menu_id){
    $ci = get_instance(); //  mengambil library yang ada di ci, tanpa ini $this tidak mengenali
    // $this diganti
    $ci->db->where('role_id',$role_id);
    $ci->db->where('menu_id',$menu_id);
    $result = $ci->db->get('user_access_menu');
    if($result->num_rows() > 0){
        return "checked='checked'";
    }
}