<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class InventoryPages extends CI_Controller {
  public function __construct(){ parent::__construct(); $this->load->database(); }

  public function incoming(){
    $q = $this->db->query("
      SELECT m.*
      FROM mainstock m
      WHERE m.type='transfer' AND m.transfer_status IN ('IN_TRANSIT','PENDING')
      ORDER BY m.mainstockcreate_date DESC, m.mainstockID DESC
    ");
    $data['transfers'] = $q->result();
    $this->load->view('inventory/transfers_incoming', $data);
  }

  public function outgoing(){
    $q = $this->db->query("
      SELECT m.*
      FROM mainstock m
      WHERE m.type='transfer'
      ORDER BY m.mainstockcreate_date DESC, m.mainstockID DESC
    ");
    $data['transfers'] = $q->result();
    $this->load->view('inventory/transfers_outgoing', $data);
  }
}
