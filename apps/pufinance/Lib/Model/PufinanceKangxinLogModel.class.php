<?php

/**
 * 康欣（金农）接口请求记录模型
 */
class PufinanceKangxinLogModel extends Model
{

    public function addPostLog($service, $data)
    {
        return $this->add(array(
            'service' => $service,
            'post_data' => $data,
            'post_time' => time(),
        ));
    }

    public function updateReqLog($id, $data, $status)
    {
        return $this->where(array('id' => $id))->save(array(
            'req_data' => $data,
            'req_status' => $status,
            'req_time' => time(),
        ));
    }
}