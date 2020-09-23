<?php
class ModelPaymentPaysafecash extends Model
{
    // create paysafecash table
    public function install()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."paysafecash` (
                `order_id` int(11) UNSIGNED NOT NULL,
                `payment_id` varchar(255) NOT NULL,
                `status` varchar(255) NOT NULL,
                `last_update` datetime NOT NULL,
                `refunded` tinyint(1) NOT NULL,
                `refunded_date` datetime NOT NULL,
                `ref_id` varchar(255) NOT NULL,
                UNIQUE KEY `order_id` (`order_id`,`payment_id`),
                KEY `status` (`status`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
        $query = $this->db->query($sql);
    }

    // drop paysafecash table
    public function uninstall()
    {
        $query = $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paysafecash`");
    }

    // check if table is created
    public function checkInstall()
    {
        $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "paysafecash'");
        return (boolean)$result->num_rows;
    }

    // get all orders that has been made with paysafe
    public function listorders($data=[])
    {
        $sql = "SELECT o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status,
        o.shipping_code,
        o.total,
        o.customer_id,
        o.email,
        o.store_name,
        o.currency_code,
        o.currency_value,
        o.date_added,
        o.date_modified,
        psc.payment_id,
        psc.refunded,
        psc.refunded_date,
        psc.ref_id,
        (SELECT osp.name FROM " . DB_PREFIX . "order_status osp WHERE osp.order_status_id = psc.status AND osp.language_id = '" . (int)$this->config->get('config_language_id') . "') AS psc_status
        FROM `" . DB_PREFIX . "order` o
        INNER JOIN " . DB_PREFIX . "paysafecash psc ON psc.order_id = o.order_id ";

        if (isset($data['filter_order_status'])) {
            $implode = [];

            $order_statuses = explode(',', $data['filter_order_status']);

            foreach ($order_statuses as $order_status_id) {
                $implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
            }

            if ($implode) {
                $sql .= " WHERE (" . implode(" OR ", $implode) . ")";
            } else {
            }
        } else {
            $sql .= " WHERE o.order_status_id > '0'";
        }


        if (!empty($data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_customer'])) {
            $sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }

        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if (!empty($data['filter_date_modified'])) {
            $sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
        }

        if (!empty($data['filter_paymentid'])) {
            $sql .= " AND psc.payment_id LIKE '" . $this->db->escape($data['filter_paymentid']) . "%'";
        }

        $sql .= " GROUP BY order_id";

        $sort_data = [
            'o.order_id',
            'status',
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY o.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'ASC')) {
            $sql .= " ASC";
        } else {
            $sql .= " DESC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    // count all orders that has been made with paysafe
    public function countorders($data = [])
    {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` o INNER JOIN " . DB_PREFIX . "paysafecash psc ON psc.order_id = o.order_id ";

        if (!empty($data['filter_order_status'])) {
            $implode = [];

            $order_statuses = explode(',', $data['filter_order_status']);

            foreach ($order_statuses as $order_status_id) {
                $implode[] = "order_status_id = '" . (int)$order_status_id . "'";
            }

            if ($implode) {
                $sql .= " WHERE (" . implode(" OR ", $implode) . ")";
            }
        } else {
            $sql .= " WHERE order_status_id > '0'";
        }

        if (!empty($data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_customer'])) {
            $sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }

        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if (!empty($data['filter_date_modified'])) {
            $sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
        }

        if (!empty($data['filter_paymentid'])) {
            $sql .= " AND psc.payment_id LIKE '" . $this->db->escape($data['filter_paymentid']) . "%'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    // update tables on refund and add order history
    public function doRefund($order_id, $payment_id, $data)
    {
        if ($order_id > 0 && $payment_id != '') {
            $this->db->query("UPDATE `" . DB_PREFIX . "paysafecash` SET refunded= '1', refunded_date = NOW(), ref_id= '" . $this->db->escape($data['ref_id']) . "' WHERE order_id = '" . (int)$order_id . "' AND payment_id= '" . $this->db->escape($payment_id) . "'");
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$data['status'] . "' WHERE order_id = '" . (int)$order_id . "'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$data['status'] . "', notify = '0', comment = '" . $this->db->escape($data['ref_id']) . "', date_added = NOW()");
        }
    }
}
