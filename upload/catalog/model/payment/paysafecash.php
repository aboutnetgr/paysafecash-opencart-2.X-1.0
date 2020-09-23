<?php
class ModelPaymentPaysafecash extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('payment/paysafecash');

        // check if settings (country) is enabled for order's country
        if (in_array($address['iso_code_2'], $this->config->get('paysafecash_countries'))) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = [];

        if ($status) {
            $method_data = [
                'code' => 'paysafecash',
                'title' => 'Paysafe:cash',
                'terms' => $this->language->get('payment_description'),
                'sort_order' => $this->config->get('paysafecash_sort_order')
            ];
        }
        return $method_data;
    }

    // update or insert paysafe order
    public function validatePaymentOrder($order_id, $status, $payment_id)
    {
        $row = $this->getPaymentOrder($order_id);
        if ($row) {
            $sql = "UPDATE `" . DB_PREFIX . "paysafecash` SET payment_id= '" . $this->db->escape($payment_id) . "', status= '" . $this->db->escape($status) . "',  last_update = NOW() WHERE order_id = '" . (int)$order_id . "'";
        } else {
            $sql = "INSERT INTO `" .DB_PREFIX . "paysafecash` SET order_id= '" . (int)$order_id . "', payment_id= '" . $this->db->escape($payment_id) . "', status= '" . $this->db->escape($status) . "', last_update = NOW()";
        }
        $this->db->query($sql);
    }

    // get order by order id
    public function getPaymentOrder($order_id)
    {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "paysafecash` WHERE order_id = '" . (int)$order_id . "'")->row;
    }

    // get order by order id and payment id
    public function getPaymentAndOrder($order_id, $payment_id)
    {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "paysafecash` WHERE order_id = '" . (int)$order_id . "' AND payment_id= '" . $this->db->escape($payment_id) . "'")->row;
    }
}
