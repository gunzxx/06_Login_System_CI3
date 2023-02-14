<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model{
    public function getUser()
    {
        $query = "
                    SELECT user.*, role.role
                    FROM user
                    JOIN role
                    ON user.role_id = role.id
                    WHERE user.role_id = {$this->session->userdata('role_id')}
                    ORDER BY role.id
                ";
        return $this->db->query($query)->row_array();
    }
}