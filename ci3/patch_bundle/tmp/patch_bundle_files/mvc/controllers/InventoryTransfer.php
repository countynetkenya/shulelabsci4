<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
class InventoryTransfer extends CI_Controller {
    public function __construct(){ parent::__construct(); $this->load->database(); $this->load->helper('inventory'); }
    private function require_post(){ if(strtoupper($_SERVER['REQUEST_METHOD'])!=='POST'){ show_404(); exit; } }

    public function accept($mainstockID){
        $this->require_post();
        $this->db->trans_start();
        $header = $this->db->query("SELECT * FROM mainstock WHERE mainstockID=? FOR UPDATE", [$mainstockID])->row();
        if(!$header){ $this->db->trans_complete(); show_error('Transfer not found',404); return; }
        if(!in_array($header->transfer_status, ['IN_TRANSIT','PENDING'])){ $this->db->trans_complete(); show_error('Invalid status',400); return; }
        $this->db->query("UPDATE stock SET is_inbound_pending=0 WHERE mainstockID=? AND quantity>0 AND is_canceled=0", [$mainstockID]);
        $this->db->query("UPDATE mainstock SET transfer_status='RECEIVED', received_by=?, received_at=NOW() WHERE mainstockID=?", [0,$mainstockID]);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) { show_error('DB error',500); } else { echo json_encode(['ok'=>true,'mainstockID'=>$mainstockID]); }
    }

    public function reject($mainstockID){
        $this->require_post();
        $reason = $this->input->post('reason') ?: 'No reason';
        $this->db->trans_start();
        $header = $this->db->query("SELECT * FROM mainstock WHERE mainstockID=? FOR UPDATE", [$mainstockID])->row();
        if(!$header){ $this->db->trans_complete(); show_error('Transfer not found',404); return; }
        if($header->transfer_status!=='IN_TRANSIT'){ $this->db->trans_complete(); show_error('Invalid status',400); return; }
        $this->db->query("UPDATE stock SET is_canceled=1 WHERE mainstockID=? AND quantity>0", [$mainstockID]);
        $this->db->query("UPDATE mainstock SET transfer_status='REJECTED', rejected_by=?, rejected_at=NOW(), reject_reason=? WHERE mainstockID=?", [0,$reason,$mainstockID]);
        $items = $this->db->query("SELECT productID, SUM(CASE WHEN quantity<0 THEN ABS(quantity) ELSE 0 END) qty_out FROM stock WHERE mainstockID=? GROUP BY productID", [$mainstockID])->result();
        if($items){
          $this->db->query("INSERT INTO mainstock (schoolID, stocktowarehouseID, type, memo, mainstockcreate_date) VALUES (?,?, 'adjustment', ?, NOW())",
                           [$header->schoolID, $header->stockfromwarehouseID, 'Auto reversal of transfer_ref=' . $header->transfer_ref]);
          $newMainID = $this->db->insert_id();
          foreach($items as $it){
            $this->db->query("INSERT INTO stock (productID, quantity, mainstockID) VALUES (?,?,?)", [$it->productID, $it->qty_out, $newMainID]);
          }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) { show_error('DB error',500); } else { echo json_encode(['ok'=>true,'mainstockID'=>$mainstockID]); }
    }
}