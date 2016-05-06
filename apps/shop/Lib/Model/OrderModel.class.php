<?php

class OrderModel extends Model {

    public function addOrderLog($order_id, $oplog, $user) {
        $data = array(
            'order_id' => $order_id,
            'oplog' => $oplog,
            'opuser' => $user,
            'optime' => date("Y-m-d H:i:s")
        );
        return M('order_log')->add($data);
    }

}

?>